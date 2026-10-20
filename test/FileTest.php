<?php

use PHPUnit\Framework\TestCase;

/**
 * File test
 */
class FileTest extends TestCase
{

	/**
	 * @var pCloud\Sdk\App
	 */
	private $pCloudApp;

	/**
	 * @var pCloud\Sdk\File
	 */
	private $instance;

	/**
	 * @return void
	 */
	public function setUp(): void
	{
		$access_token = getenv('ACCESS_TOKEN');
		$locationid = intval(getenv('LOCATION'));

		$this->pCloudApp = new pCloud\Sdk\App();
		$this->pCloudApp->setAccessToken($access_token);
		$this->pCloudApp->setLocationId($locationid);

		pCloud\Sdk\Config::$curllib = "pCloud\Sdk\TestCurl";
		$this->instance = new pCloud\Sdk\File($this->pCloudApp);
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
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testGetFileInfo()
	{
		$fileid = 1234;

		$expected = $this->buildExpected("checksumfile", array("fileid" => $fileid));
		$query = $this->instance->getInfo($fileid);

		self::assertEquals($expected, $query);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testGetFileLink()
	{
		$fileid = 1234;

		$expected = $this->buildExpected("getfilelink", array("fileid" => $fileid));
		$query = $this->instance->getLink($fileid);

		self::assertEquals($expected, $query);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testDeleteFile()
	{
		$fileid = 1234;

		$expected = $this->buildExpected("deletefile", array("fileid" => $fileid));
		$query = $this->instance->delete($fileid);

		self::assertEquals($expected, $query);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testRenameFile()
	{
		$fileid = 1234;
		$name = "fileName";

		$expected = $this->buildExpected("renamefile", array("fileid" => $fileid, "toname" => $name));
		$query = $this->instance->rename($fileid, $name);

		self::assertEquals($expected, $query);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testMoveFile()
	{
		$fileid = 1234;
		$destination = 12;

		$expected = $this->buildExpected("renamefile", array("fileid" => $fileid, "tofolderid" => $destination));
		$query = $this->instance->move($fileid, $destination);

		self::assertEquals($expected, $query);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testCopyFile()
	{
		$fileid = 1234;
		$destination = 12;

		$expected = $this->buildExpected("copyfile", array("fileid" => $fileid, "tofolderid" => $destination));
		$query = $this->instance->copy($fileid, $destination);

		self::assertEquals($expected, $query);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testDownloadFile()
	{
		$fileid = 1234;
		$destination = sys_get_temp_dir();
		if ($destination === '') $destination = "/tmp";

		$result = $this->instance->download($fileid, $destination);

		self::assertEquals('boolean', gettype($result));
	}

	/**
	 * @param $method
	 * @param $params
	 *
	 * @return string
	 */
	private function buildExpected($method, $params): string
	{
		$expected = pCloud\Sdk\Config::getApiHostByLocationId($this->pCloudApp->getLocationId()) . $method;

		if ($params !== array()) {
			$expected .= "?" . http_build_query($params);
		}

		return $expected;
	}
}