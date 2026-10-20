<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . DIRECTORY_SEPARATOR . "AssertsRequestUrl.php";

/**
 * Folders tests
 */
final class FolderTest extends TestCase
{
	use AssertsRequestUrl;

	private pCloud\Sdk\App $pCloudApp;

	private pCloud\Sdk\Folder $instance;

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

	public function tearDown(): void
	{
		unset($this->instance);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testListRoot(): void
	{
		$expected = $this->buildExpected("listfolder", array("folderid" => 0));
		$contents = $this->instance->listRoot();

		self::assertCount(1, $contents);
		self::assertRequestUrl($expected, $contents[0]);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testListFolder(): void
	{
		$folderid = 1234;

		$expected = $this->buildExpected("listfolder", array("folderid" => $folderid));
		$contents = $this->instance->getContent($folderid);

		self::assertCount(1, $contents);
		self::assertRequestUrl($expected, $contents[0]);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testlistFolderByName(): void
	{
		$folderName = "folderName";

		$expected = array("url" => $this->buildExpected("listfolder", array("folderid" => 0)));

		$query = $this->instance->listFolder($folderName);

		self::assertSame($expected, $query);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testCreateFolder(): void
	{
		$folderid = 1234;
		$name = "folderName";

		$expected = $this->buildExpected("createfolder", array("name" => $name, "folderid" => $folderid));
		$query = $this->instance->create($name, $folderid);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testRenameFolder(): void
	{
		$folderid = 1234;
		$name = "newName";

		$expected = $this->buildExpected("renamefolder", array("toname" => $name, "folderid" => $folderid));
		$query = $this->instance->rename($folderid, $name);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testMoveFolder(): void
	{
		$folderid = 1234;
		$destination = 12;

		$expected = $this->buildExpected("renamefolder", array("tofolderid" => $destination, "folderid" => $folderid));
		$query = $this->instance->move($folderid, $destination);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testDeleteFolder(): void
	{
		$folderid = 1234;

		$expected = $this->buildExpected("deletefolder", array("folderid" => $folderid));
		$query = $this->instance->delete($folderid);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws \pCloud\Sdk\Exception
	 */
	public function testDeleteRecursiveFolder(): void
	{
		$folderid = 1234;

		$expected = $this->buildExpected("deletefolderrecursive", array("folderid" => $folderid));
		$query = $this->instance->deleteRecursive($folderid);

		self::assertRequestUrl($expected, $query);
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