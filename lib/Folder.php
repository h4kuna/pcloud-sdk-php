<?php declare(strict_types = 1);

namespace pCloud\Sdk;

use InvalidArgumentException;
use stdClass;
use function array_pop;
use function array_reverse;
use function boolval;
use function explode;
use function is_array;
use function is_bool;
use function is_int;
use function is_null;
use function is_string;
use function pathinfo;
use function strlen;
use function strncmp;
use function strval;
use const DIRECTORY_SEPARATOR;
use const PATHINFO_DIRNAME;
use const PATHINFO_EXTENSION;
use const PHP_EOL;

/**
 * Folder Class
 */
final class Folder
{

	/**
	 * Holds the Request class
	 */
	private Request $request;

	/**
	 * Main class constructor
	 */
	public function __construct(App $app)
	{
		$this->request = new Request($app);
	}

	/**
	 * Get folder meta data
	 *
	 * @throws Exception
	 */
	public function getMetadata(int $folderId): stdClass
	{
		return $this->request->get('listfolder', ['folderid' => $folderId]);
	}

	/**
	 * Search for folder
	 *
	 * @throws Exception
	 */
	public function search(string $path): stdClass
	{
		$path = pathinfo($path, PATHINFO_DIRNAME);
		// $path = str_replace(DIRECTORY_SEPARATOR,"\\",$path);

		echo 'Searching for folder: ' . $path . PHP_EOL;

		$params = [
			'nofiles' => 1,
			'path' => $path,
		];

		return $this->request->get('listfolder', $params);
	}

	/**
	 * list files in folder identified by name
	 * (start from the root, match directory name, ...)
	 *
	 * @return array<mixed>|null
	 *
	 * @throws Exception
	 */
	public function listFolder(?string $folder = null): ?array
	{
		// first compare with each folder in root
		if (is_null($folder)) {
			return $this->getContent(0);
		}

		$extension = strval(pathinfo($folder, PATHINFO_EXTENSION));
		if ($extension !== '') {
			$folder = strval(pathinfo($folder, PATHINFO_DIRNAME));
		}

		$pathParts = array_reverse(explode(DIRECTORY_SEPARATOR, $folder));

		$directory = null;
		$currentFolderId = 0;

		while (($folderName = array_pop($pathParts))) { /* current directory */
			$folderItems = $this->getContent($currentFolderId);

			foreach ($folderItems as $item) {
				$directory = (array) $item;
				if (isset($directory['isfolder']) && boolval($directory['isfolder']) === true) {
					$name = $directory['name'] ?? null;
					$folderId = $directory['folderid'] ?? null;
					if (is_string($name) && $name !== '' && is_int($folderId) && strncmp($folderName, $name, strlen($folderName)) === 0) {
						$currentFolderId = $folderId;
						break;
					}
				}
			}
		}

		return $directory;
	}

	/**
	 * List root folders
	 *
	 * @return array<mixed>
	 *
	 * @throws Exception
	 */
	public function listRoot(): array
	{
		return $this->getContent(0);
	}

	/**
	 * Get folder content
	 *
	 * @return array<mixed>
	 *
	 * @throws Exception
	 */
	public function getContent(int $folderId): array
	{
		$folderMetadata = $this->getMetadata($folderId);

		$metadata = $folderMetadata->metadata ?? null;
		$contents = $metadata instanceof stdClass ? ($metadata->contents ?? null) : null;

		if (!is_array($contents)) {
			throw new Exception('Failed to get folder content!');
		}

		return $contents;
	}

	/**
	 * @return stdClass|int Folder ID or response Data
	 *
	 * @throws Exception
	 */
	public function create(
		?string $name,
		?int $parent = 0,
	): stdClass|int
	{
		if ($name === null || $name === '') {
			throw new InvalidArgumentException('Please, provide valid folder name');
		}

		$params = [
			'name' => $name,
			'folderid' => $parent,
		];

		$response = $this->request->get('createfolder', $params);

		return $this->folderIdOrResponse($response);
	}

	/**
	 * Rename folder
	 *
	 * @throws Exception
	 */
	public function rename(
		int $folderId,
		?string $name,
	): stdClass|int
	{
		if ($name === null || $name === '') {
			throw new InvalidArgumentException('Please, provide folder name');
		}

		$params = [
			'toname' => $name,
			'folderid' => $folderId,
		];

		$response = $this->request->get('renamefolder', $params);

		return $this->folderIdOrResponse($response);
	}

	/**
	 * Move folder
	 *
	 * @throws Exception
	 */
	public function move(
		int $folderId,
		int $newParent,
	): stdClass|int
	{
		$params = [
			'tofolderid' => $newParent,
			'folderid' => $folderId,
		];

		$response = $this->request->get('renamefolder', $params);

		return $this->folderIdOrResponse($response);
	}

	/**
	 * Delete folder
	 *
	 * @throws Exception
	 */
	public function delete(int $folderId): bool|stdClass
	{
		$response = $this->request->get('deletefolder', ['folderid' => $folderId]);

		$metadata = $response->metadata ?? null;
		$isDeleted = $metadata instanceof stdClass ? ($metadata->isdeleted ?? null) : null;

		return is_bool($isDeleted) ? $isDeleted : $response;
	}

	/**
	 * Delete recursive
	 *
	 * @throws Exception
	 */
	public function deleteRecursive(int $folderId): stdClass
	{
		return $this->request->get('deletefolderrecursive', ['folderid' => $folderId]);
	}

	/**
	 * Picks the folder ID from the response metadata
	 *
	 * @return stdClass|int Folder ID, or the whole response if it carries none
	 */
	private function folderIdOrResponse(stdClass $response): stdClass|int
	{
		$metadata = $response->metadata ?? null;
		$folderId = $metadata instanceof stdClass ? ($metadata->folderid ?? null) : null;

		return is_int($folderId) ? $folderId : $response;
	}

}
