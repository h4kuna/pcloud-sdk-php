<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . DIRECTORY_SEPARATOR . "ErrorCurl.php";

/**
 * Exceptions triggering tests
 */
final class ExceptionTest extends TestCase
{

	/**
	 * @var pCloud\Sdk\App
	 */
	private $pCloudApp;

	/**
	 * Set up the initial needed data before running the test
	 */
	public function setUp(): void
	{
		$access_token = getenv('ACCESS_TOKEN');
		self::assertIsString($access_token);
		$locationid = intval(getenv('LOCATION'));

		$this->pCloudApp = new pCloud\Sdk\App();
		$this->pCloudApp->setAccessToken($access_token);
		$this->pCloudApp->setLocationId($locationid);

		pCloud\Sdk\Config::$curllib = "ErrorCurl";
	}

	/**
	 * @return void
	 */
	public function tearDown(): void
	{
		pCloud\Sdk\Config::$curllib = "pCloud\Sdk\Curl";
	}

	/**
	 * @param string|null $name
	 *
	 * @throws \pCloud\Sdk\Exception
	 */
	#[DataProvider('provideCreateData')]
	public function testCreateFolder(?string $name = "testFolder"): void
	{
		$this->expectException(Exception::class);
		$folder = new pCloud\Sdk\Folder($this->pCloudApp);
		$folder->create($name);
	}

	/**
	 * @param int $folderId
	 * @param string|null $name
	 *
	 * @throws \pCloud\Sdk\Exception
	 */
	#[DataProvider('provideRenameData')]
	public function testRenameFolder(int $folderId = 0, ?string $name = "testFolder"): void
	{
		$this->expectException(Exception::class);
		$folder = new pCloud\Sdk\Folder($this->pCloudApp);
		$folder->rename($folderId, $name);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	#[DataProvider('provideId')]
	public function testDeleteFolder(int $folderId): void
	{
		$this->expectException(Exception::class);
		$folder = new pCloud\Sdk\Folder($this->pCloudApp);
		$folder->delete($folderId);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testDeleteRecursiveFolder(): void
	{
		$this->expectException(Exception::class);
		$folder = new pCloud\Sdk\Folder($this->pCloudApp);
		$folder->deleteRecursive(-1);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	#[DataProvider('provideId')]
	public function testGetFileLink(int $fileId): void
	{
		$this->expectException(Exception::class);
		$file = new pCloud\Sdk\File($this->pCloudApp);
		$file->getLink($fileId);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	#[DataProvider('provideId')]
	public function testDeleteFile(int $fileId): void
	{
		$this->expectException(Exception::class);
		$file = new pCloud\Sdk\File($this->pCloudApp);
		$file->delete($fileId);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	#[DataProvider('provideMoveData')]
	public function testMoveFile(int $fileId, int $folderId): void
	{
		$this->expectException(Exception::class);
		$file = new pCloud\Sdk\File($this->pCloudApp);
		$file->move($fileId, $folderId);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	#[DataProvider('provideId')]
	public function testGetFileInfo(int $fileId): void
	{
		$this->expectException(Exception::class);
		$file = new pCloud\Sdk\File($this->pCloudApp);
		$file->getInfo($fileId);
	}

	/**
	 * @return array<int, array<int, mixed>>
	 */
	public static function provideId(): array
	{
		return array(
			array(0),
			array(1)
		);
	}

	/**
	 * @return array<int, array<int, mixed>>
	 */
	public static function provideRenameData(): array
	{
		return array(
			array(0, ""),
			array(0, "invalid/name"),
			array(0, null),
			array(1, ""),
			array(1, "New name"),
		);
	}

	/**
	 * @return array<int, array<int, mixed>>
	 */
	public static function provideCreateData(): array
	{
		return array(
			array(""),
			array("invalid/name"),
			array(null)
		);
	}

	/**
	 * @return array<int, array<int, mixed>>
	 */
	public static function provideMoveData(): array
	{
		return array(
			array(0, 1),
			array(1, 0),
			array(0, 0)
		);
	}

	/**
	 * @return array<int, array<int, mixed>>
	 */
	public static function provideUploadData(): array
	{
		return array(
			array(dirname(__FILE__) . DIRECTORY_SEPARATOR . "office pic 2015-09-28 16-44-30.jpeg", null),
			array("not existing file", 0),
			array(dirname(__FILE__) . DIRECTORY_SEPARATOR . "office pic 2015-09-28 16-44-30.jpeg", 1),
			array(dirname(__FILE__) . DIRECTORY_SEPARATOR, 0),
			array(false, 0)
		);
	}

	/**
	 * @return array<int, array<int, mixed>>
	 */
	public static function provideDownloadData(): array
	{
		return array(
			array(false, dirname(__FILE__)),
			array(null, dirname(__FILE__)),
			array(1, ""),
			array(1, null),
			array(0, false)
		);
	}
}