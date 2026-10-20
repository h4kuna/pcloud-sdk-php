<?php

declare(strict_types=1);

namespace pCloud\Sdk;

/**
 * Configuration class
 * Here you will find some basic API related configuration parameters.
 * They are provided and setup by pCloud, and shouldn't be modified.
 *
 * @package pCloud\Sdk
 */
class Config {
	
	/**
	 * @var string
	 */
	static public $usHost = "https://api.pcloud.com/";
	/**
	 * @var string
	 */
	static public $euHost = "https://eapi.pcloud.com/";
	/**
	 * @var class-string<Curl>
	 */
	static public $curllib = "pCloud\Sdk\Curl";
	/**
	 * @var int<1, max>
	 */
	static public $filePartSize = 10485760;

	/**
	 * @param int $locationid
	 * @return string
	 */
	static public function getApiHostByLocationId(int $locationid): string
	{
		if ($locationid === 2) {
			return self::$euHost;
		} else {
			return self::$usHost;
		}
	}
}