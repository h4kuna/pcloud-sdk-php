<?php declare(strict_types = 1);

namespace pCloud\Sdk;

use CurlHandle;
use InvalidArgumentException;
use stdClass;
use Throwable;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt;
use function intval;
use function is_bool;
use function is_string;
use function range;
use const CURLINFO_CONTENT_TYPE;
use const CURLINFO_HTTP_CODE;
use const CURLINFO_RESPONSE_CODE;
use const CURLOPT_HEADER;

/**
 * cURL class
 * this class manages the main cURL functionality
 */
class Curl
{

	/**
	 * Holds the cURL
	 */
	protected CurlHandle $curl;

	/**
	 * Array of header data
	 *
	 * @var array<int, string> $headers
	 */
	private array $headers = [];

	/**
	 * @throws Exception
	 */
	public function __construct(string $url)
	{
		if ($url === '') {
			throw new InvalidArgumentException('Invalid URL');
		}

		$curl = curl_init($url);
		if ($curl === false) {
			throw new Exception("Couldn't initialize cURL");
		}

		$this->curl = $curl;
	}

	/**
	 * Set cURL option
	 */
	public function setOption(
		int $option,
		mixed $value,
	): void
	{
		curl_setopt($this->curl, $option, $value);
	}

	/**
	 * Add aditional header to be sent with the cURL request
	 */
	public function addHeader(string $header): void
	{
		$this->headers[] = $header;
	}

	/**
	 * The main method, executes cURL exec and throws exception if anything fails
	 *
	 * @throws Exception
	 */
	public function exec(): stdClass
	{
		$this->setOption(CURLOPT_HEADER, $this->headers);

		$r = $this->curlReconnect();

		$httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		$contentType = curl_getinfo($this->curl, CURLINFO_CONTENT_TYPE);

		if (!is_bool($r)) {
			$response = new Response($r, $httpCode, is_string($contentType) ? $contentType : '');
			return $response->get();
		} else {
			throw new Exception('Connection lost!');
		}
	}

	/**
	 * Simple "retry" function based on the response status code
	 */
	private function curlReconnect(): bool|string
	{
		try {
			foreach (range(0, 3) as $ignored) {
				$response = curl_exec($this->curl);
				if (intval(curl_getinfo($this->curl, CURLINFO_RESPONSE_CODE)) === 200) {
					return $response;
				}
			}
			return false;
		} catch (Throwable $e) {
			return false;
		}
	}

}
