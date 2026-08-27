<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One-time CSRF ticket consumed by /api/proxy.
 *
 * DDL (created 2026-08-27 on the EC2 local MySQL, schema `pa-v3`;
 * user pa_proxy has SELECT/INSERT/DELETE on this table only):
 *
 *   CREATE TABLE `csrf_token` (
 *     `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 *     `token` CHAR(64) NOT NULL,
 *     `ip` VARCHAR(45) NOT NULL DEFAULT '',
 *     `created_time` INT UNSIGNED NOT NULL,
 *     `expires_time` INT UNSIGNED NOT NULL,
 *     PRIMARY KEY (`id`),
 *     UNIQUE KEY `uk_token` (`token`)
 *   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 */
class CsrfToken extends Model
{
	protected $table = 'csrf_token';

	public $timestamps = false;

	protected $fillable = ['token', 'ip', 'created_time', 'expires_time'];
}
