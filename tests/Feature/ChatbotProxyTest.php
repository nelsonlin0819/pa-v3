<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChatbotProxyTest extends TestCase {
	use RefreshDatabase;

	protected function setUp(): void {
		parent::setUp();

		if (!Schema::hasTable('csrf_token')) {
			Schema::create('csrf_token', function ($table) {
				$table->string('token', 64)->primary();
				$table->string('ip')->nullable();
				$table->integer('created_time');
				$table->integer('expires_time');
			});
		}
	}

	protected function issueTicket(): string {
		$res = $this->postJson('/api/csrf');
		$res->assertOk()->assertJson(['ok' => true]);
		return $res->json('token');
	}

	public function test_proxy_translates_action_to_mcp_tools_call_and_relays_envelope_verbatim(): void {
		$envelope = [
			'jsonrpc' => '2.0',
			'id'      => 123,
			'result'  => [
				'content' => [
					['type' => 'text', 'text' => json_encode(['found' => true, 'transaction' => ['id' => 1], 'refunds' => [], 'refunded_total' => '0.00'])],
				],
			],
		];

		Http::fake([
			'*' => Http::response(json_encode($envelope), 200, ['Content-Type' => 'application/json']),
		]);

		$res = $this->postJson('/api/proxy', [
			'action' => 'transaction.query',
			'params' => ['keyword' => 'PAY-20260826-0001'],
		], ['X-Csrf-Token' => $this->issueTicket()]);

		$res->assertOk();
		$this->assertSame($envelope, $res->json());

		Http::assertSent(function ($request) {
			$body = $request->data();

			return $request['method'] === 'tools/call'
				&& $body['jsonrpc'] === '2.0'
				&& $body['params']['name'] === 'TransactionQuery'
				&& $body['params']['arguments']['keyword'] === 'PAY-20260826-0001'
				&& is_int($body['params']['arguments']['from'])
				&& is_int($body['params']['arguments']['to']);
		});
	}

	public function test_proxy_maps_refund_action_to_transaction_refund_tool(): void {
		Http::fake(['*' => Http::response(json_encode(['jsonrpc' => '2.0', 'id' => 1, 'result' => ['content' => []]]), 200)]);

		$this->postJson('/api/proxy', [
			'action' => 'transaction.refund',
			'params' => ['keyword' => '123', 'merchant_reference' => 'M-1'],
		], ['X-Csrf-Token' => $this->issueTicket()]);

		Http::assertSent(function ($request) {
			return $request['method'] === 'tools/call'
				&& $request->data()['params']['name'] === 'TransactionRefund'
				&& $request->data()['params']['arguments']['merchant_reference'] === 'M-1';
		});
	}

	public function test_proxy_requires_merchant_reference_for_refund(): void {
		$res = $this->postJson('/api/proxy', [
			'action' => 'transaction.refund',
			'params' => ['keyword' => '123'],
		], ['X-Csrf-Token' => $this->issueTicket()]);

		$res->assertStatus(400)->assertJson(['ok' => false, 'error' => 'merchant_reference_required']);
	}

	public function test_proxy_rejects_unknown_action(): void {
		$res = $this->postJson('/api/proxy', [
			'action' => 'nope',
			'params' => [],
		], ['X-Csrf-Token' => $this->issueTicket()]);

		$res->assertStatus(400)->assertJson(['ok' => false, 'error' => 'unknown_action']);
	}

	public function test_proxy_maps_the_other_mcp_tools_without_range_injection(): void {
		Http::fake(['*' => Http::response(json_encode(['jsonrpc' => '2.0', 'id' => 1, 'result' => ['content' => []]]), 200)]);

		$calls = [
			['transaction.date-summary', ['date' => '2026-09-01', 'currency' => 'HKD'], 'TransactionDateSummary'],
			['merchant.list', ['keyword' => 'acme'], 'MerchantList'],
			['merchant.info', ['merchant' => 'ACME001'], 'MerchantInfo'],
			['order.status', ['merchant_reference' => 'M-1'], 'OrderStatusByMerchantReference'],
		];

		foreach ($calls as [$action, $params, $tool]) {
			$this->postJson('/api/proxy', ['action' => $action, 'params' => $params], ['X-Csrf-Token' => $this->issueTicket()])
				->assertOk();

			Http::assertSent(function ($request) use ($tool, $params) {
				$body = $request->data();

				return $request['method'] === 'tools/call'
					&& $body['params']['name'] === $tool
					&& $body['params']['arguments'] === $params; // no from/to injected
			});
		}
	}

	public function test_proxy_rejects_missing_csrf_ticket(): void {
		$this->postJson('/api/proxy', ['action' => 'transaction.query', 'params' => ['keyword' => 'x']])
			->assertStatus(401)->assertJson(['ok' => false, 'error' => 'csrf_token_required']);
	}

	public function test_csrf_ticket_is_single_use(): void {
		$ticket = $this->issueTicket();

		$this->postJson('/api/proxy', ['action' => 'nope', 'params' => []], ['X-Csrf-Token' => $ticket])
			->assertStatus(400); // consumed by the unknown-action check

		$this->postJson('/api/proxy', ['action' => 'nope', 'params' => []], ['X-Csrf-Token' => $ticket])
			->assertStatus(401)->assertJson(['error' => 'invalid_or_expired_csrf_token']);
	}
}
