<?php declare(strict_types = 1);

namespace pCloud\Sdk;

/**
 * Configuration class
 * Here you will find some basic API related configuration parameters.
 * They are provided and setup by pCloud, and shouldn't be modified.
 */
final class Config
{

	/**
	 * @var class-string<Curl>
	 */
	public static string $curlLib = Curl::class;

	/**
	 * @var int<1, max>
	 */
	public static int $filePartSize = 10_485_760;

}
