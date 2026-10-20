<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . DIRECTORY_SEPARATOR . "AssertsRequestUrl.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "MemoryStream.php";

/**
 * File test
 */
final class FileTest extends TestCase
{
	use AssertsRequestUrl;

	private pCloud\Sdk\App $pCloudApp;

	private pCloud\Sdk\File $instance;

	public function setUp(): void
	{
		$access_token = getenv('ACCESS_TOKEN');
		self::assertIsString($access_token);
		$locationid = intval(getenv('LOCATION'));

		$this->pCloudApp = new pCloud\Sdk\App();
		$this->pCloudApp->setAccessToken($access_token);
		$this->pCloudApp->setLocationId($locationid);

		pCloud\Sdk\Config::$curlLib = "pCloud\Sdk\TestCurl";
		$this->instance = new pCloud\Sdk\File($this->pCloudApp);
	}

	public function tearDown(): void
	{
		unset($this->instance);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testGetFileInfo(): void
	{
		$fileid = 1234;

		$expected = $this->buildExpected("checksumfile", array("fileid" => $fileid));
		$query = $this->instance->getInfo($fileid);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testGetFileLink(): void
	{
		$fileid = 1234;

		$query = $this->instance->getLink($fileid);

		self::assertSame("https://test.pcloud.com/file.txt", $query);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testDeleteFile(): void
	{
		$fileid = 1234;

		$expected = $this->buildExpected("deletefile", array("fileid" => $fileid));
		$query = $this->instance->delete($fileid);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testRenameFile(): void
	{
		$fileid = 1234;
		$name = "fileName";

		$expected = $this->buildExpected("renamefile", array("fileid" => $fileid, "toname" => $name));
		$query = $this->instance->rename($fileid, $name);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testMoveFile(): void
	{
		$fileid = 1234;
		$destination = 12;

		$expected = $this->buildExpected("renamefile", array("fileid" => $fileid, "tofolderid" => $destination));
		$query = $this->instance->move($fileid, $destination);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testCopyFile(): void
	{
		$fileid = 1234;
		$destination = 12;

		$expected = $this->buildExpected("copyfile", array("fileid" => $fileid, "tofolderid" => $destination));
		$query = $this->instance->copy($fileid, $destination);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testDownloadFile(): void
	{
		$fileid = 1234;
		$destination = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid("pcloud-sdk-");

		MemoryStream::$content = "file content";

		stream_wrapper_unregister("https");
		stream_wrapper_register("https", MemoryStream::class);

		try {
			$result = $this->instance->download($fileid, $destination);
		} finally {
			stream_wrapper_restore("https");
		}

		$path = $destination . DIRECTORY_SEPARATOR . "file.txt";

		self::assertTrue($result);
		self::assertSame("file content", file_get_contents($path));

		unlink($path);
		rmdir($destination);
	}

	/**
	 * @param array<string, int|string> $params
	 */
	private function buildExpected(string $method, array $params): string
	{
		$expected = pCloud\Sdk\Config::getApiHostByLocationId($this->pCloudApp->getLocationId()) . $method;

		if ($params !== array()) {
			$expected .= "?" . http_build_query($params);
		}

		return $expected;
	}
}