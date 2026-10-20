<?php declare(strict_types = 1);

namespace pCloud\Sdk\Tests;

use pCloud\Sdk\App;
use pCloud\Sdk\Config;
use pCloud\Sdk\Exception;
use pCloud\Sdk\File;
use pCloud\Sdk\TestCurl;
use PHPUnit\Framework\TestCase;
use function file_get_contents;
use function getenv;
use function http_build_query;
use function intval;
use function rmdir;
use function stream_wrapper_register;
use function stream_wrapper_restore;
use function stream_wrapper_unregister;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use const DIRECTORY_SEPARATOR;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'AssertsRequestUrl.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'MemoryStream.php';

/**
 * File test
 */
final class FileTest extends TestCase
{

	use AssertsRequestUrl;

	private App $pCloudApp;

	private File $instance;

	public function setUp(): void
	{
		$accessToken = getenv('ACCESS_TOKEN');
		self::assertIsString($accessToken);
		$locationId = intval(getenv('LOCATION'));

		$this->pCloudApp = new App();
		$this->pCloudApp->setAccessToken($accessToken);
		$this->pCloudApp->setLocationId($locationId);

		Config::$curlLib = TestCurl::class;
		$this->instance = new File($this->pCloudApp);
	}

	public function tearDown(): void
	{
		unset($this->instance);
	}

	/**
	 * @throws Exception
	 */
	public function testGetFileInfo(): void
	{
		$fileid = 1234;

		$expected = $this->buildExpected('checksumfile', ['fileid' => $fileid]);
		$query = $this->instance->getInfo($fileid);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws Exception
	 */
	public function testGetFileLink(): void
	{
		$fileid = 1234;

		$query = $this->instance->getLink($fileid);

		self::assertSame('https://test.pcloud.com/file.txt', $query);
	}

	/**
	 * @throws Exception
	 */
	public function testDeleteFile(): void
	{
		$fileid = 1234;

		$expected = $this->buildExpected('deletefile', ['fileid' => $fileid]);
		$query = $this->instance->delete($fileid);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws Exception
	 */
	public function testRenameFile(): void
	{
		$fileid = 1234;
		$name = 'fileName';

		$expected = $this->buildExpected('renamefile', ['fileid' => $fileid, 'toname' => $name]);
		$query = $this->instance->rename($fileid, $name);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws Exception
	 */
	public function testMoveFile(): void
	{
		$fileid = 1234;
		$destination = 12;

		$expected = $this->buildExpected('renamefile', ['fileid' => $fileid, 'tofolderid' => $destination]);
		$query = $this->instance->move($fileid, $destination);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws Exception
	 */
	public function testCopyFile(): void
	{
		$fileid = 1234;
		$destination = 12;

		$expected = $this->buildExpected('copyfile', ['fileid' => $fileid, 'tofolderid' => $destination]);
		$query = $this->instance->copy($fileid, $destination);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws Exception
	 */
	public function testDownloadFile(): void
	{
		$fileid = 1234;
		$destination = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('pcloud-sdk-');

		MemoryStream::$content = 'file content';

		stream_wrapper_unregister('https');
		stream_wrapper_register('https', MemoryStream::class);

		try {
			$result = $this->instance->download($fileid, $destination);
		} finally {
			stream_wrapper_restore('https');
		}

		$path = $destination . DIRECTORY_SEPARATOR . 'file.txt';

		self::assertTrue($result);
		self::assertSame('file content', file_get_contents($path));

		unlink($path);
		rmdir($destination);
	}

	/**
	 * @param array<string, int|string> $params
	 */
	private function buildExpected(
		string $method,
		array $params,
	): string
	{
		$expected = $this->pCloudApp->getLocation()->value . $method;

		if ($params !== []) {
			$expected .= '?' . http_build_query($params);
		}

		return $expected;
	}

}
