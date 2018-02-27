<?php

/**
 * @author heminei
 */

namespace HemiFrame\Interfaces;

interface Session {

	public function getId(): string;

	public function getName(): string;

	public function getLifeTime(): int;

	public function getCookiePath(): string;

	public function getCookieDomain(): string;

	public function getCookieSecure(): bool;

	public function getCookieHttpOnly(): bool;

	public function setName(string $name);

	public function setLifeTime(int $lifeTime);

	public function setCookiePath(string $cookiePath);

	public function setCookieDomain(string$cookieDomain);

	public function setCookieSecure(bool $cookieSecure);

	public function setCookieHttpOnly(bool $cookieHttpOnly);

	public function start();

	public function save();

	public function destroy();

	public function __get(string $name);

	public function __set(string $name, $value);

	public function get(string $name);

	public function set(string $name, $value);
}
