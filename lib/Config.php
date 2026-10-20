<?php declare(strict_types = 1);

namespace pCloud\Sdk;

/**
 * Configuration class
 * Here you will find some basic API related configuration parameters.
 * They are provided and setup by pCloud, and shouldn't be modified.
 */
final class Config
{

	public static string $usHost = 'https://api.pcloud.com/';
	public static string $euHost = 'https://eapi.pcloud.com/';

	/**
	 * @var class-string<Curl>
	 */
	public static string $curlLib = 'pCloud\Sdk\Curl';

	/**
	 * @var int<1, max>
	 */
	public static int $filePartSize = 10_485_760;

	public static function getApiHostByLocationId(int $locationid): string
	{
		if ($locationid === 2) {
			return self::$euHost;
		} else {
			return self::$usHost;
		}
	}

}
