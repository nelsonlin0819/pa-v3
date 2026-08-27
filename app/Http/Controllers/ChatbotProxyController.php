<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Chatbot proxy API (Laravel port of the flat csrf.php / proxy.php).
 *
 *   POST /api/csrf   (optional X-Turnstile-Token when Turnstile is enabled)
 *     -> one-time ticket {token, expires_in, expires_at}, recorded in
 *        `pa-v3`.`csrf_token`, TTL 600s, expired rows cleaned on issue.
 *
 *   POST /api/proxy  (X-Csrf-Token: <ticket>, Content-Type: application/json)
 *     -> ticket consumed atomically, body relayed to the IT-internal chatbot
 *        gateway API; upstream body + HTTP status returned verbatim.
 */
class ChatbotProxyController extends Controller {
	public const TOKEN_TTL = 600;

	public const ALLOWED_ACTIONS = ['transaction.query', 'transaction.refund'];

	public function usage(): JsonResponse {
		return response()->json([
			'ok'      => true,
			'service' => 'chatbot-proxy',
			'usage'   => [
				'method'    => 'POST',
				'endpoints' => [
					'/api/csrf'  => ['X-Turnstile-Token (when Turnstile is enabled)'],
					'/api/proxy' => ['Content-Type: application/json', 'X-Csrf-Token: <one-time ticket from /api/csrf>'],
				],
				'actions' => [
					'transaction.query'  => ['keyword (required)', 'from (YYYY-MM-DD)', 'to (YYYY-MM-DD)'],
					'transaction.refund' => ['keyword (required)', 'from (YYYY-MM-DD)', 'to (YYYY-MM-DD)'],
				],
			],
		]);
	}

	public function issueCsrf(Request $request): JsonResponse {
		if ((string) config('services.turnstile.secret') !== '') {
			$turnstileToken = (string) $request->header('X-Turnstile-Token', '');
			if ($turnstileToken === '' || !$this->verifyTurnstile($turnstileToken, $request)) {
				return response()->json(['ok' => false, 'error' => 'turnstile_verification_failed'], 403);
			}
		}

		$now = time();
		DB::table('csrf_token')->where('expires_time', '<', $now)->delete();

		$token = bin2hex(random_bytes(32));
		DB::table('csrf_token')->insert([
			'token'        => $token,
			'ip'           => $this->clientIp($request),
			'created_time' => $now,
			'expires_time' => $now + self::TOKEN_TTL,
		]);

		return response()->json([
			'ok'         => true,
			'token'      => $token,
			'expires_in' => self::TOKEN_TTL,
			'expires_at' => $now + self::TOKEN_TTL,
		]);
	}

	public function proxy(Request $request) {
		$csrf = (string) $request->header('X-Csrf-Token', '');
		if (strlen($csrf) !== 64 || !ctype_xdigit($csrf)) {
			return response()->json(['ok' => false, 'error' => 'csrf_token_required'], 401);
		}

		$consumed = DB::table('csrf_token')
			->where('token', $csrf)
			->where('expires_time', '>=', time())
			->delete();
		if ($consumed !== 1) {
			return response()->json(['ok' => false, 'error' => 'invalid_or_expired_csrf_token'], 401);
		}

		$data = json_decode((string) $request->getContent(), true);
		if (!is_array($data)) {
			return response()->json(['ok' => false, 'error' => 'invalid_json'], 400);
		}

		$action = (string) ($data['action'] ?? '');
		if (!in_array($action, self::ALLOWED_ACTIONS, true)) {
			return response()->json(['ok' => false, 'error' => 'unknown_action', 'available' => self::ALLOWED_ACTIONS], 400);
		}

		$params = is_array($data['params'] ?? null) ? $data['params'] : [];
		$params = $this->withDefaultRange($params);

		try {
			$upstream = Http::timeout(30)
				->connectTimeout(10)
				->withToken((string) config('services.chatbot-upstream.key'))
				->post((string) config('services.chatbot-upstream.url'), [
					'action' => $action,
					'params' => $params,
				]);
		} catch (\Throwable $e) {
			\report($e);
			return response()->json(['ok' => false, 'error' => 'upstream_unreachable', 'message' => $e->getMessage()], 502);
		}

		return response($upstream->body(), $upstream->status(), ['Content-Type' => 'application/json']);
	}

	/**
	 * An unscoped reference search full-scans the transactions table on the
	 * gateway (>30s), so default the window to today (Asia/Taipei, sent as
	 * unix timestamps to avoid cross-server timezone drift) whenever either
	 * bound is missing.
	 */
	protected function withDefaultRange(array $params): array {
		$todayStart = (new \DateTimeImmutable('today 00:00:00', new \DateTimeZone('Asia/Taipei')))->getTimestamp();
		if (!isset($params['from']) || $params['from'] === '') {
			$params['from'] = $todayStart;
		}
		if (!isset($params['to']) || $params['to'] === '') {
			$params['to'] = $todayStart + 86399;
		}
		return $params;
	}

	protected function verifyTurnstile(string $token, Request $request): bool {
		try {
			$res = Http::asForm()
				->timeout(10)
				->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
					'secret'   => (string) config('services.turnstile.secret'),
					'response' => $token,
					'remoteip' => $this->clientIp($request),
				]);

			$result = $res->json();
			return is_array($result) && ($result['success'] ?? false) === true;
		} catch (\Throwable $e) {
			return false;
		}
	}

	/**
	 * Real client IP behind Cloudflare (audit only — tickets are not IP-bound
	 * because the CF egress POP can differ between the issue and consume call).
	 */
	protected function clientIp(Request $request): string {
		$ip = (string) $request->header('CF-Connecting-IP', '');
		return $ip !== '' ? $ip : (string) $request->ip();
	}
}
