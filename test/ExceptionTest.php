<?php declare(strict_types = 1);

namespace pCloud\Sdk\Tests;

use pCloud\Sdk\App;
use pCloud\Sdk\Config;
use pCloud\Sdk\Curl;
use pCloud\Sdk\Exception;
use pCloud\Sdk\File;
use pCloud\Sdk\Folder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;
use function dirname;
use function getenv;
use function intval;
use const DIRECTORY_SEPARATOR;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'ErrorCurl.php';

/**
 * Exceptions triggering tests
 */
final class ExceptionTest extends TestCase
{

	private App $pCloudApp;

	/**
	 * Set up the initial needed data before running the test
	 */
	public function setUp(): void
	{
		$accessToken = getenv('ACCESS_TOKEN');
		self::assertIsString($accessToken);
		$locationId = intval(getenv('LOCATION'));

		$this->pCloudApp = new App();
		$this->pCloudApp->setAccessToken($accessToken);
		$this->pCloudApp->setLocationId($locationId);

		Config::$curlLib = ErrorCurl::class;
	}

	public function tearDown(): void
	{
		Config::$curlLib = Curl::class;
	}

	/**
	 * @throws Exception
	 */
	#[DataProvider('provideCreateData')]
	public function testCreateFolder(?string $name = 'testFolder'): void
	{
		$this->expectException(Throwable::class);
		$folder = new Folder($this->pCloudApp);
		$folder->create($name);
	}

	/**
	 * @throws Exception
	 */
	#[DataProvider('provideRenameData')]
	public function testRenameFolder(
		int $folderId = 0,
		?string $name = 'testFolder',
	): void
	{
		$this->expectException(Throwable::class);
		$folder = new Folder($this->pCloudApp);
		$folder->rename($folderId, $name);
	}

	/**
	 * @throws Exception
	 */
	#[DataProvider('provideId')]
	public function testDeleteFolder(int $folderId): void
	{
		$this->expectException(Throwable::class);
		$folder = new Folder($this->pCloudApp);
		$folder->delete($folderId);
	}

	/**
	 * @throws Exception
	 */
	public function testDeleteRecursiveFolder(): void
	{
		$this->expectException(Throwable::class);
		$folder = new Folder($this->pCloudApp);
		$folder->deleteRecursive(-1);
	}

	/**
	 * @throws Exception
	 */
	#[DataProvider('provideId')]
	public function testGetFileLink(int $fileId): void
	{
		$this->expectException(Throwable::class);
		$file = new File($this->pCloudApp);
		$file->getLink($fileId);
	}

	/**
	 * @throws Exception
	 */
	#[DataProvider('provideId')]
	public function testDeleteFile(int $fileId): void
	{
		$this->expectException(Throwable::class);
		$file = new File($this->pCloudApp);
		$file->delete($fileId);
	}

	/**
	 * @throws Exception
	 */
	#[DataProvider('provideMoveData')]
	public function testMoveFile(
		int $fileId,
		int $folderId,
	): void
	{
		$this->expectException(Throwable::class);
		$file = new File($this->pCloudApp);
		$file->move($fileId, $folderId);
	}

	/**
	 * @throws Exception
	 */
	#[DataProvider('provideId')]
	public function testGetFileInfo(int $fileId): void
	{
		$this->expectException(Throwable::class);
		$file = new File($this->pCloudApp);
		$file->getInfo($fileId);
	}

	/**
	 * @return array<int, array<int, mixed>>
	 */
	public static function provideId(): array
	{
		return [
			[0],
			[1],
		];
	}

	/**
	 * @return array<int, array<int, mixed>>
	 */
	public static function provideRenameData(): array
	{
		return [
			[0, ''],
			[0, 'invalid/name'],
			[0, null],
			[1, ''],
			[1, 'New name'],
		];
	}

	/**
	 * @return array<int, array<int, mixed>>
	 */
	public static function provideCreateData(): array
	{
		return [
			[''],
			['invalid/name'],
			[null],
		];
	}

	/**
	 * @return array<int, array<int, mixed>>
	 */
	public static function provideMoveData(): array
	{
		return [
			[0, 1],
			[1, 0],
			[0, 0],
		];
	}

	/**
	 * @return array<int, array<int, mixed>>
	 */
	public static function provideUploadData(): array
	{
		return [
			[dirname(__FILE__) . DIRECTORY_SEPARATOR . 'office pic 2015-09-28 16-44-30.jpeg', null],
			['not existing file', 0],
			[dirname(__FILE__) . DIRECTORY_SEPARATOR . 'office pic 2015-09-28 16-44-30.jpeg', 1],
			[dirname(__FILE__) . DIRECTORY_SEPARATOR, 0],
			[false, 0],
		];
	}

	/**
	 * @return array<int, array<int, mixed>>
	 */
	public static function provideDownloadData(): array
	{
		return [
			[false, dirname(__FILE__)],
			[null, dirname(__FILE__)],
			[1, ''],
			[1, null],
			[0, false],
		];
	}

}
