<?php

declare(strict_types=1);

namespace pCloud\Sdk;

use stdClass;

/**
 * This class is used for testing perposes
 *
 * @noinspection PhpUnused
 */
final class TestCurl extends Curl
{

	/**
	 * @noinspection PhpUnused
	 */
	function __construct(string $url)
	{
		parent::__construct($url);
	}

	/**
	 * @noinspection PhpUnused
	 */
	public function exec(): stdClass
	{
		$url = curl_getinfo($this->curl, CURLINFO_EFFECTIVE_URL);
		$url = $this->parseUrl($url);

		$item = new stdClass();
		$item->url = $url;

		$metadata = new stdClass();
		$metadata->contents = array($item);

		$result = new stdClass();
		$result->url = $url;
		$result->hosts = array("test.pcloud.com");
		$result->path = "/file.txt";
		$result->metadata = $metadata;

		return $result;
	}

	/**
	 * Parse URL
	 */
	private function parseUrl(string $url): string
	{

		$parts = explode("?", $url);

		if (count($parts) === 1) {
			return strval($parts[0]);
		} else {
			parse_str($parts[1], $query);
			unset($query["access_token"]);
			$parts[1] = http_build_query($query);
			return strlen($parts[1]) === 0 ? $parts[0] : implode("?", $parts);
		}
	}
}