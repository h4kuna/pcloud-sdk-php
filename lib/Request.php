<?php declare(strict_types = 1);

namespace pCloud\Sdk;

use stdClass;
use function array_merge;
use function http_build_query;
use const CURLOPT_BINARYTRANSFER;
use const CURLOPT_CONNECTTIMEOUT;
use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_SSL_VERIFYPEER;
use const CURLOPT_TIMEOUT;
use const CURLOPT_USERAGENT;

/**
 * Class Request
 */
final class Request
{

	/**
	 * pCloud api endpoint host
	 */
	private string $host;

	/**
	 * Global parameters, initially access token is in this variable,
	 * later additional parameters are applied
	 *
	 * @var array<string, mixed> $globalParams
	 */
	private array $globalParams;

	/**
	 * cURL function execution timeout ( in seconds )
	 */
	private int $curlExecutionTimeout;

	/**
	 * collects the host and the initial global parameters
	 */
	public function __construct(App $app)
	{
		$this->host = $app->getLocation()->value;
		$this->globalParams = [
			'access_token' => $app->getAccessToken(),
		];

		$this->curlExecutionTimeout = $app->getCurlExecutionTimeout();
	}

	/**
	 * GET method
	 *
	 * @param array<string, mixed> $params
	 *
	 * @throws Exception
	 */
	public function get(
		string $method,
		array $params = [],
	): stdClass
	{
		$curl = $this->buildCurl($this->prepareUrl($method, array_merge($this->globalParams, $params)));

		return $curl->exec();
	}

	/**
	 * Post method
	 *
	 * @param array<string, mixed>|null $params
	 *
	 * @throws Exception
	 */
	public function post(
		string $method,
		?array $params = [],
	): stdClass
	{
		$curl = $this->buildCurl($this->prepareUrl($method, $this->globalParams));

		$curl->setOption(CURLOPT_CUSTOMREQUEST, 'POST');
		$curl->setOption(CURLOPT_POSTFIELDS, $params);

		return $curl->exec();
	}

	/**
	 * Put method, will mostly be used for file upload
	 *
	 * @param array<string, mixed> $params
	 *
	 * @throws Exception
	 */
	public function put(
		string $method,
		string $content,
		array $params = [],
	): stdClass
	{
		$curl = $this->buildCurl($this->prepareUrl($method, array_merge($this->globalParams, $params)));

		$curl->setOption(CURLOPT_CUSTOMREQUEST, 'PUT');
		$curl->setOption(CURLOPT_POSTFIELDS, $content);
		$curl->setOption(CURLOPT_HTTPHEADER, ['Content-Type: text/html']);
		$curl->setOption(CURLOPT_BINARYTRANSFER, true); // TODO: deprecated !

		return $curl->exec();
	}

	/**
	 * Setup new Curl instance
	 *
	 * @param string $endPoint the final cURL URL endpoint
	 */
	private function buildCurl(string $endPoint): Curl
	{
		$curl = new Config::$curlLib($endPoint);

		$curl->setOption(CURLOPT_USERAGENT, 'pCloud PHP SDK');
		$curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
		$curl->setOption(CURLOPT_RETURNTRANSFER, true);

		// The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
		$curl->setOption(CURLOPT_CONNECTTIMEOUT, 20);

		// The maximum number of seconds to allow cURL functions to execute.
		$curl->setOption(CURLOPT_TIMEOUT, $this->curlExecutionTimeout);

		return $curl;
	}

	/**
	 * This method combines the host with the API method and if there are some additional parameters.
	 *
	 * @param array<string, mixed> $params
	 */
	private function prepareUrl(
		string $method,
		array $params = [],
	): string
	{
		$url = $this->host . $method;

		if ($params !== []) {
			$url .= '?' . http_build_query($params);
		}

		return $url;
	}

}
