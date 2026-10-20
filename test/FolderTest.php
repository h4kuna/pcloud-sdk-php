<?php declare(strict_types = 1);

namespace pCloud\Sdk\Tests;

use pCloud\Sdk\App;
use pCloud\Sdk\Config;
use pCloud\Sdk\Exception;
use pCloud\Sdk\Folder;
use pCloud\Sdk\TestCurl;
use PHPUnit\Framework\TestCase;
use function getenv;
use function http_build_query;
use function intval;
use const DIRECTORY_SEPARATOR;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'AssertsRequestUrl.php';

/**
 * Folders tests
 */
final class FolderTest extends TestCase
{

	use AssertsRequestUrl;

	private App $pCloudApp;

	private Folder $instance;

	public function setUp(): void
	{
		$accessToken = getenv('ACCESS_TOKEN');
		self::assertIsString($accessToken);
		$locationId = intval(getenv('LOCATION'));

		$this->pCloudApp = new App();
		$this->pCloudApp->setAccessToken($accessToken);
		$this->pCloudApp->setLocationId($locationId);

		Config::$curlLib = TestCurl::class;
		$this->instance = new Folder($this->pCloudApp);
	}

	public function tearDown(): void
	{
		unset($this->instance);
	}

	/**
	 * @throws Exception
	 */
	public function testListRoot(): void
	{
		$expected = $this->buildExpected('listfolder', ['folderid' => 0]);
		$contents = $this->instance->listRoot();

		self::assertCount(1, $contents);
		self::assertRequestUrl($expected, $contents[0]);
	}

	/**
	 * @throws Exception
	 */
	public function testListFolder(): void
	{
		$folderid = 1234;

		$expected = $this->buildExpected('listfolder', ['folderid' => $folderid]);
		$contents = $this->instance->getContent($folderid);

		self::assertCount(1, $contents);
		self::assertRequestUrl($expected, $contents[0]);
	}

	/**
	 * @throws Exception
	 */
	public function testlistFolderByName(): void
	{
		$folderName = 'folderName';

		$expected = ['url' => $this->buildExpected('listfolder', ['folderid' => 0])];

		$query = $this->instance->listFolder($folderName);

		self::assertSame($expected, $query);
	}

	/**
	 * @throws Exception
	 */
	public function testCreateFolder(): void
	{
		$folderid = 1234;
		$name = 'folderName';

		$expected = $this->buildExpected('createfolder', ['name' => $name, 'folderid' => $folderid]);
		$query = $this->instance->create($name, $folderid);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws Exception
	 */
	public function testRenameFolder(): void
	{
		$folderid = 1234;
		$name = 'newName';

		$expected = $this->buildExpected('renamefolder', ['toname' => $name, 'folderid' => $folderid]);
		$query = $this->instance->rename($folderid, $name);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws Exception
	 */
	public function testMoveFolder(): void
	{
		$folderid = 1234;
		$destination = 12;

		$expected = $this->buildExpected('renamefolder', ['tofolderid' => $destination, 'folderid' => $folderid]);
		$query = $this->instance->move($folderid, $destination);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws Exception
	 */
	public function testDeleteFolder(): void
	{
		$folderid = 1234;

		$expected = $this->buildExpected('deletefolder', ['folderid' => $folderid]);
		$query = $this->instance->delete($folderid);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @throws Exception
	 */
	public function testDeleteRecursiveFolder(): void
	{
		$folderid = 1234;

		$expected = $this->buildExpected('deletefolderrecursive', ['folderid' => $folderid]);
		$query = $this->instance->deleteRecursive($folderid);

		self::assertRequestUrl($expected, $query);
	}

	/**
	 * @param array<string, int|string> $params
	 */
	private function buildExpected(
		string $method,
		array $params,
	): string
	{
		$expected = Config::getApiHostByLocationId($this->pCloudApp->getLocationId()) . $method;

		if ($params !== []) {
			$expected .= '?' . http_build_query($params);
		}

		return $expected;
	}

}
