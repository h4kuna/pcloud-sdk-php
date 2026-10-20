<?php declare(strict_types = 1);

namespace pCloud\Sdk;

use stdClass;
use function end;
use function explode;
use function fclose;
use function feof;
use function file_exists;
use function fopen;
use function fread;
use function fwrite;
use function is_array;
use function is_bool;
use function is_dir;
use function is_file;
use function is_int;
use function is_readable;
use function is_string;
use function mkdir;
use function rawurldecode;
use function rename;
use function sleep;
use function str_replace;
use const DIRECTORY_SEPARATOR;

/**
 * Class File
 *
 * this class provides API calls related to file manipulation
 */
final class File
{

	/**
	 * The size in bytes of each uploaded/downloaded chunk
	 *
	 * @var int<1, max> $partSize
	 */
	private int $partSize;

	/**
	 * Holds the Request class
	 */
	private Request $request;

	/**
	 * File Class constructor
	 */
	public function __construct(App $app)
	{
		$this->request = new Request($app);
		$this->partSize = Config::$filePartSize;
	}

	/**
	 * Get link ( using File ID )
	 *
	 * @throws Exception
	 */
	public function getLink(int $fileId): string
	{
		$params = [
			'fileid' => $fileId,
		];

		$response = $this->request->get('getfilelink', $params);

		$hosts = $response->hosts ?? null;
		$path = $response->path ?? null;

		if (is_array($hosts) && is_string($hosts[0] ?? null) && is_string($path)) {
			return 'https://' . $hosts[0] . $path;
		}

		throw new Exception('Failed to get file link!');
	}

	/**
	 * Download file
	 *
	 * @param int $fileId File ID
	 * @param string $destination The destination, where the file will be stored!
	 *
	 * @throws Exception
	 */
	public function download(
		int $fileId,
		string $destination = '',
	): bool
	{
		$fileLink = $this->getLink($fileId);

		if ($destination !== '') {
			$destination = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $destination) . DIRECTORY_SEPARATOR;
		}

		if ($destination !== '' && !is_dir($destination)) {
			if (!mkdir($destination)) {
				throw new Exception("Couldn't create destination folder");
			}
		}

		$parts = explode('/', $fileLink);
		$path = $destination . rawurldecode(end($parts));

		$source = fopen($fileLink, 'rb');
		$file = fopen($path . '.download', 'wb');
		if ($source === false || $file === false) {
			throw new Exception("Couldn't open the source or the destination file");
		}

		while (!feof($source)) {
			$content = fread($source, $this->partSize);
			if ($content === false) {
				throw new Exception("Couldn't read the file content");
			}
			fwrite($file, $content);
		}
		fclose($file);
		fclose($source);

		return rename($path . '.download', $path);
	}

	/**
	 * @throws Exception
	 */
	public function upload(
		string $path,
		int $folderId = 0,
		?string $filename = null,
	): stdClass
	{
		if (!file_exists($path) || !is_file($path) || !is_readable($path)) {
			throw new Exception('Invalid file');
		}

		$path = str_replace(['\\', '_'], DIRECTORY_SEPARATOR, $path);
		$parts = explode(DIRECTORY_SEPARATOR, $path);

		if ($filename === null || $filename === '') {
			$filename = end($parts);
		}

		$upload = $this->createUpload();

		$uploadId = $upload->uploadid ?? null;
		if (!is_int($uploadId)) {
			throw new Exception('File -> upload -> "createUpload" not returning the expected data!');
		}

		$params = [
			'uploadid' => $uploadId,
			'uploadoffset' => 0,
		];

		$numFailures = 0;

		$file = fopen($path, 'r');
		if ($file === false) {
			throw new Exception("Couldn't open the file");
		}

		while (!feof($file)) {
			$content = fread($file, $this->partSize);
			if ($content === false) {
				throw new Exception("Couldn't read the file content");
			}
			do {
				try {
					$this->write($content, $params);

					$params['uploadoffset'] += $this->partSize;

					$numFailures = 0;
					continue 2;

				} catch (Exception $e) {
					$numFailures++;
					sleep(3);
				}

			} while ($numFailures < 10);

			break;
		}

		if (@fclose($file)) {
			return $this->save($uploadId, $filename, $folderId);
		}

		return new stdClass();
	}

	/**
	 * Delete file ( using file ID )
	 *
	 * @throws Exception
	 */
	public function delete(int $fileId): bool|stdClass
	{
		$response = $this->request->get('deletefile', ['fileid' => $fileId]);

		$metadata = $response->metadata ?? null;
		$isDeleted = $metadata instanceof stdClass ? ($metadata->isdeleted ?? null) : null;

		return is_bool($isDeleted) ? $isDeleted : $response;
	}

	/**
	 * Rename file ( using file ID )
	 *
	 * @throws Exception
	 */
	public function rename(
		int $fileId,
		string $name,
	): stdClass
	{
		if ($name === '') {
			throw new Exception('Please, provide valid file name!');
		}

		$params = [
			'fileid' => $fileId,
			'toname' => $name,
		];

		return $this->request->get('renamefile', $params);
	}

	/**
	 * Moves file
	 *
	 * @throws Exception
	 */
	public function move(
		int $fileId,
		int $folderId,
	): stdClass
	{
		$params = [
			'fileid' => $fileId,
			'tofolderid' => $folderId,
		];

		return $this->request->get('renamefile', $params);
	}

	/**
	 * Copy file
	 *
	 * @throws Exception
	 */
	public function copy(
		int $fileId,
		int $folderId,
	): stdClass
	{
		$params = [
			'fileid' => $fileId,
			'tofolderid' => $folderId,
		];

		return $this->request->get('copyfile', $params);
	}

	/**
	 * Get file info ( using file ID )
	 *
	 * @throws Exception
	 */
	public function getInfo(int $fileId): stdClass
	{
		return $this->request->get('checksumfile', ['fileid' => $fileId]);
	}

	/**
	 * Prepare to initiate Upload process
	 *
	 * @throws Exception
	 */
	private function createUpload(): stdClass
	{
		return $this->request->get('upload_create');
	}

	/**
	 * @throws Exception
	 */
	private function save(
		int $uploadId,
		string $name,
		int $folderId,
	): stdClass
	{
		$params = [
			'uploadid' => $uploadId,
			'name' => $name,
			'folderid' => $folderId,
		];

		return $this->request->get('uploadsave', $params);
	}

	/**
	 * Upload - write content chunk
	 *
	 * @param array<string, mixed> $params
	 *
	 * @throws Exception
	 */
	private function write(
		string $content,
		array $params,
	): void
	{
		$this->request->put('uploadwrite', $content, $params);
	}

}
