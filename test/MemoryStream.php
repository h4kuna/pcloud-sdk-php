<?php declare(strict_types = 1);

namespace pCloud\Sdk\Tests;

use function strlen;
use function substr;

/**
 * Stream wrapper, which serves the content of $content instead of the real remote file
 */
final class MemoryStream
{

	/**
	 * @var resource|null
	 */
	public $context;

	public static string $content = '';

	private int $position = 0;

	public function stream_open(
		string $path,
		string $mode,
		int $options,
		?string &$openedPath,
	): bool
	{
		$this->position = 0;

		return true;
	}

	public function stream_read(int $count): string
	{
		$chunk = substr(self::$content, $this->position, $count);
		$this->position += strlen($chunk);

		return $chunk;
	}

	public function stream_eof(): bool
	{
		return $this->position >= strlen(self::$content);
	}

	/**
	 * @return array<string, int>
	 */
	public function stream_stat(): array
	{
		return ['size' => strlen(self::$content)];
	}

}
