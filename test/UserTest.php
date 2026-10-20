<?php declare(strict_types = 1);

namespace pCloud\Sdk\Tests;

use pCloud\Sdk\App;
use pCloud\Sdk\Config;
use pCloud\Sdk\Exception;
use pCloud\Sdk\TestCurl;
use pCloud\Sdk\User;
use PHPUnit\Framework\TestCase;
use function getenv;
use function intval;
use const DIRECTORY_SEPARATOR;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'AssertsRequestUrl.php';

/**
 * User tests
 */
final class UserTest extends TestCase
{

	use AssertsRequestUrl;

	private App $pCloudApp;

	private User $instance;

	/**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		$accessToken = getenv('ACCESS_TOKEN');
		self::assertIsString($accessToken);
		$locationId = intval(getenv('LOCATION'));

		$this->pCloudApp = new App();
		$this->pCloudApp->setAccessToken($accessToken);
		$this->pCloudApp->setLocationId($locationId);

		Config::$curlLib = TestCurl::class;
		$this->instance = new User($this->pCloudApp);
	}

	public function tearDown(): void
	{
		unset($this->instance);
	}

	public function testUserInfo(): void
	{
		$expected = Config::getApiHostByLocationId($this->pCloudApp->getLocationId()) . 'userinfo';
		$query = $this->instance->getUserInfo();

		self::assertRequestUrl($expected, $query);
	}

}
