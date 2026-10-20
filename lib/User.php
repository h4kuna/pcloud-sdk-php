<?php

declare(strict_types=1);

namespace pCloud\Sdk;

use stdClass;

/**
 * User class
 */
final class User
{

	/**
	 * Holds the Request class
	 */
	private Request $request;

	private stdClass $userInfo;

	/**
	 * Class constructor
	 *
	 * @throws Exception
	 */
	function __construct(App $app)
	{
		$this->request = new Request($app);
		$this->userInfo = $this->request->get("userinfo");
	}

	/**
	 * Get the full user info
	 *
	 * @noinspection PhpUnused
	 */
	public function getUserInfo(): stdClass
	{
		return $this->userInfo;
	}

	/**
	 * Get ther User ID
	 *
	 * @throws Exception
	 * @noinspection PhpUnused
	 */
	public function getUserId(): int
	{
		return $this->intValue($this->userInfo->userid ?? null, "userid");
	}

	/**
	 * Get the user Email Address
	 *
	 * @throws Exception
	 * @noinspection PhpUnused
	 */
	public function getUserEmail(): string
	{
		return $this->stringValue($this->userInfo->email ?? null, "email");
	}

	/**
	 * Get the used quota
	 *
	 * @throws Exception
	 * @noinspection PhpUnused
	 */
	public function getUsedQuota(): int
	{
		return $this->intValue($this->userInfo->usedquota ?? null, "usedquota");
	}

	/**
	 * Get quota
	 *
	 * @throws Exception
	 * @noinspection PhpUnused
	 */
	public function getQuota(): int
	{
		return $this->intValue($this->userInfo->quota ?? null, "quota");
	}

	/**
	 * Get user's public links quota
	 *
	 * @throws Exception
	 * @noinspection PhpUnused
	 */
	public function getPublicLinkQuota(): int
	{
		return $this->intValue($this->userInfo->publiclinkquota ?? null, "publiclinkquota");
	}

	/**
	 * @throws Exception
	 */
	private function intValue(mixed $value, string $field): int
	{
		if (!is_int($value)) {
			throw new Exception(sprintf("Unexpected value of %s in user info", $field));
		}

		return $value;
	}

	/**
	 * @throws Exception
	 */
	private function stringValue(mixed $value, string $field): string
	{
		if (!is_string($value)) {
			throw new Exception(sprintf("Unexpected value of %s in user info", $field));
		}

		return $value;
	}
}
