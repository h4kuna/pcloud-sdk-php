<?php

namespace pCloud\Sdk;

use stdClass;

/**
 * User class
 *
 * @package pCloud\Sdk
 */
class User
{

	/**
	 * Holds the Request class
	 *
	 * @var Request $request
	 */
	private $request;

	/**
	 * @var stdClass $userInfo
	 */
	private $userInfo;

	/**
	 * Class constructor
	 *
	 * @param App $app
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
	 * @return stdClass
	 * @noinspection PhpUnused
	 */
	public function getUserInfo(): stdClass
	{
		return $this->userInfo;
	}

	/**
	 * Get ther User ID
	 *
	 * @return int
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
	 * @return string
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
	 * @return int
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
	 * @return int
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
	 * @return int
	 * @throws Exception
	 * @noinspection PhpUnused
	 */
	public function getPublicLinkQuota(): int
	{
		return $this->intValue($this->userInfo->publiclinkquota ?? null, "publiclinkquota");
	}

	/**
	 * @param mixed $value
	 * @param string $field
	 *
	 * @return int
	 * @throws Exception
	 */
	private function intValue($value, string $field): int
	{
		if (!is_int($value)) {
			throw new Exception(sprintf("Unexpected value of %s in user info", $field));
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 * @param string $field
	 *
	 * @return string
	 * @throws Exception
	 */
	private function stringValue($value, string $field): string
	{
		if (!is_string($value)) {
			throw new Exception(sprintf("Unexpected value of %s in user info", $field));
		}

		return $value;
	}
}
