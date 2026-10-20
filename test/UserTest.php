<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . DIRECTORY_SEPARATOR . "AssertsRequestUrl.php";

/**
 * User tests
 */
final class UserTest extends TestCase
{
	use AssertsRequestUrl;

	/**
	 * @var pCloud\Sdk\App
	 */
	private $pCloudApp;

	/**
	 * @var pCloud\Sdk\User
	 */
	private $instance;

	/**
	 * @return void
	 * @throws \pCloud\Sdk\Exception
	 */
	public function setUp(): void
	{
		$access_token = getenv('ACCESS_TOKEN');
		self::assertIsString($access_token);
		$locationid = intval(getenv('LOCATION'));

		$this->pCloudApp = new pCloud\Sdk\App();
		$this->pCloudApp->setAccessToken($access_token);
		$this->pCloudApp->setLocationId($locationid);

		pCloud\Sdk\Config::$curllib = "pCloud\Sdk\TestCurl";
		$this->instance = new pCloud\Sdk\User($this->pCloudApp);
	}

	/**
	 * @return void
	 */
	public function tearDown(): void
	{
		unset($this->instance);
	}

	/**
	 * @return void
	 */
	public function testUserInfo(): void
	{

		$expected = pCloud\Sdk\Config::getApiHostByLocationId($this->pCloudApp->getLocationId()) . "userinfo";
		$query = $this->instance->getUserInfo();

		self::assertRequestUrl($expected, $query);
	}
}