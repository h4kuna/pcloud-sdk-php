<?php declare(strict_types = 1);

namespace pCloud\Sdk;

use stdClass;
use function count;
use function curl_getinfo;
use function explode;
use function http_build_query;
use function implode;
use function parse_str;
use function strlen;
use function strval;
use const CURLINFO_EFFECTIVE_URL;

/**
 * This class is used for testing perposes
 */
final class TestCurl extends Curl
{

	public function __construct(string $url)
	{
		parent::__construct($url);
	}

	public function exec(): stdClass
	{
		$url = curl_getinfo($this->curl, CURLINFO_EFFECTIVE_URL);
		$url = $this->parseUrl($url);

		$item = new stdClass();
		$item->url = $url;

		$metadata = new stdClass();
		$metadata->contents = [$item];

		$result = new stdClass();
		$result->url = $url;
		$result->hosts = ['test.pcloud.com'];
		$result->path = '/file.txt';
		$result->metadata = $metadata;

		return $result;
	}

	/**
	 * Parse URL
	 */
	private function parseUrl(string $url): string
	{
		$parts = explode('?', $url);

		if (count($parts) === 1) {
			return strval($parts[0]);
		} else {
			parse_str($parts[1], $query);
			unset($query['access_token']);
			$parts[1] = http_build_query($query);
			return strlen($parts[1]) === 0 ? $parts[0] : implode('?', $parts);
		}
	}

}
