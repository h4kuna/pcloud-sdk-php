<?php

use PHPUnit\Framework\TestCase;

/**
 * Folders tests
 */
class FolderTest extends TestCase
{

	/**
	 * @var pCloud\Sdk\App
	 */
	private $pCloudApp;

	/**
	 * @var pCloud\Sdk\Folder
	 */
	private $instance;

	/**
	 * @return void
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
		$this->instance = new pCloud\Sdk\Folder($this->pCloudApp);
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
	public function testListRoot(): void
	{
		$expected = $this->buildExpected("listfolder", array("folderid" => 0));
		$query = $this->instance->listRoot();

		self::assertEquals($expected, $query);
	}

	/**
	 * @return void
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testListFolder(): void
	{
		$folderid = 1234;

		$expected = $this->buildExpected("listfolder", array("folderid" => $folderid));
		$query = $this->instance->getContent($folderid);

		self::assertEquals($expected, $query);
	}

	/**
	 * @return void
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testlistFolderByName(): void
	{
		$folderName = "folderName";

		$expected = [$this->buildExpected("listfolder", array("folderid" => "0"))];

		$query = $this->instance->listFolder($folderName);

		self::assertEquals($expected, $query);
	}

	/**
	 * @return void
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testCreateFolder(): void
	{
		$folderid = 1234;
		$name = "folderName";

		$expected = $this->buildExpected("createfolder", array("name" => $name, "folderid" => $folderid));
		$query = $this->instance->create($name, $folderid);

		self::assertEquals($expected, $query);
	}

	/**
	 * @return void
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testRenameFolder(): void
	{
		$folderid = 1234;
		$name = "newName";

		$expected = $this->buildExpected("renamefolder", array("toname" => $name, "folderid" => $folderid));
		$query = $this->instance->rename($folderid, $name);

		self::assertEquals($expected, $query);
	}

	/**
	 * @return void
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testMoveFolder(): void
	{
		$folderid = 1234;
		$destination = 12;

		$expected = $this->buildExpected("renamefolder", array("tofolderid" => $destination, "folderid" => $folderid));
		$query = $this->instance->move($folderid, $destination);

		self::assertEquals($expected, $query);
	}

	/**
	 * @return void
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testDeleteFolder(): void
	{
		$folderid = 1234;

		$expected = $this->buildExpected("deletefolder", array("folderid" => $folderid));
		$query = $this->instance->delete($folderid);

		self::assertEquals($expected, $query);
	}

	/**
	 * @return void
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testDeleteRecursiveFolder(): void
	{
		$folderid = 1234;

		$expected = $this->buildExpected("deletefolderrecursive", array("folderid" => $folderid));
		$query = $this->instance->deleteRecursive($folderid);

		self::assertEquals($expected, $query);
	}

	/**
	 * @param string $method
	 * @param array<string, int|string> $params
	 *
	 * @return string
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