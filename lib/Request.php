<?php

namespace pCloud\Sdk;

use stdClass;

/**
 * Class Request
 *
 * @package pCloud\Sdk
 */
class Request
{

	/**
	 * pCloud api endpoint host
	 *
	 * @var string $host
	 */
	private $host;

	/**
	 * Global parameters, initially access token is in this variable,
	 * later additional parameters are applied
	 *
	 * @var array $globalParams
	 */
	private $globalParams;

	/**
	 * cURL function execution timeout ( in seconds )
	 *
	 * @var int $curlExecutionTimeout
	 */
	private $curlExecutionTimeout;

	/**
	 * Class constructor
	 * collects the host and the initial global parameters
	 *
	 * @param App $app
	 */
	function __construct(App $app)
	{
		$this->host = Config::getApiHostByLocationId($app->getLocationId());
		$this->globalParams = array(
			"access_token" => $app->getAccessToken()
		);

		$this->curlExecutionTimeout = $app->getCurlExecutionTimeout();
	}

	/**
	 * GET method
	 *
	 * @param string $method
	 * @param array|null $params
	 *
	 * @return stdClass
	 * @throws Exception
	 * @noinspection PhpUnused
	 */
	public function get(string $method, ?array $params = array()): stdClass
	{
		$curl = $this->_buildCurl($this->_prepareURL($method, array_merge($this->globalParams, $params)));

		return $curl->exec();
	}

	/**
	 * Post method
	 *
	 * @param string $method
	 * @param array|null $params
	 *
	 * @return stdClass
	 * @throws Exception
	 * @noinspection PhpUnused
	 */
	public function post(string $method, ?array $params = array()): stdClass
	{
		$curl = $this->_buildCurl($this->_prepareURL($method, $this->globalParams));

		$curl->setOption(CURLOPT_CUSTOMREQUEST, "POST");
		$curl->setOption(CURLOPT_POSTFIELDS, $params);

		return $curl->exec();
	}

	/**
	 * Put method, will mostly be used for file upload
	 *
	 * @param string $method
	 * @param string $content
	 * @param array|null $params
	 *
	 * @return stdClass
	 * @throws Exception
	 * @noinspection PhpUnused
	 */
	public function put(string $method, string $content, ?array $params = array()): stdClass
	{

		$curl = $this->_buildCurl($this->_prepareURL($method, array_merge($this->globalParams, $params)));

		$curl->setOption(CURLOPT_CUSTOMREQUEST, "PUT");
		$curl->setOption(CURLOPT_POSTFIELDS, $content);
		$curl->setOption(CURLOPT_HTTPHEADER, array("Content-Type: text/html"));
		$curl->setOption(CURLOPT_BINARYTRANSFER, true); // TODO: deprecated !

		return $curl->exec();
	}

	/**
	 * Setup new Curl instance
	 *
	 * @param string $endPoint the final cURL URL endpoint
	 *
	 * @return Curl
	 */
	private function _buildCurl(string $endPoint): Curl
	{
		$curl = new Config::$curllib($endPoint);

		$curl->setOption(CURLOPT_USERAGENT, "pCloud PHP SDK");
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
	 * @param string $method
	 *
	 * @param array $params
	 * @return string
	 */
	private function _prepareURL(string $method, array $params = array()): string
	{
		$url = $this->host . $method;

		if ($params !== array()) {
			$url .= "?" . http_build_query($params);
		}

		return $url;
	}
}