<?php

declare(strict_types=1);

/**
 * Stream wrapper, which serves the content of $content instead of the real remote file
 */
final class MemoryStream
{

	/**
	 * @var resource|null
	 */
	public $context;

	/**
	 * @var string
	 */
	public static string $content = '';

	/**
	 * @var int
	 */
	private int $position = 0;

	/**
	 * @param string $path
	 * @param string $mode
	 * @param int $options
	 * @param string|null $openedPath
	 *
	 * @return bool
	 */
	public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
	{
		$this->position = 0;

		return true;
	}

	/**
	 * @param int $count
	 *
	 * @return string
	 */
	public function stream_read(int $count): string
	{
		$chunk = substr(self::$content, $this->position, $count);
		$this->position += strlen($chunk);

		return $chunk;
	}

	/**
	 * @return bool
	 */
	public function stream_eof(): bool
	{
		return $this->position >= strlen(self::$content);
	}

	/**
	 * @return array<string, int>
	 */
	public function stream_stat(): array
	{
		return array("size" => strlen(self::$content));
	}
}
