<?php

declare(strict_types=1);

namespace pCloud\Sdk;

use stdClass;

/**
 * Class Response
 * The main purpose of this class is to organise the cURL response data into recognizable bundle
 */
class Response
{

	/**
	 * Response data, decoded to stdClass if it is a valid JSON object
	 *
	 * @var string|stdClass $response
	 */
	private $response;

	/**
	 * @var int $httpCode
	 */
	private $httpCode;

	/**
	 * @var string $contentType
	 */
	private $contentType;

	/**
	 * Class constructor
	 *
	 * @param string $response
	 * @param int $httpCode
	 * @param string $contentType
	 */
	function __construct(string $response, int $httpCode, string $contentType)
	{
		$this->response = $response;
		$this->httpCode = $httpCode;
		$this->contentType = $contentType;

		$this->parseJson();
	}

	/**
	 * Get method
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public function get(): stdClass
	{
		if ($this->httpCode !== 200) {
			throw new Exception("HTTP Code = " . $this->httpCode);
		}

		if (!$this->response instanceof stdClass) {
			throw new Exception("Unexpected response format");
		}

		if (($this->response->result ?? null) === 0) {
			return $this->parseResponse($this->response);
		}

		$error = $this->response->error ?? null;

		throw new Exception(is_string($error) ? $error : "Unexpected response format");
	}

	/**
	 * Converts the response if it is in JSON format
	 *
	 * @return void
	 */
	private function parseJson(): void
	{
		if (is_string($this->response) && strpos($this->contentType, "application/json") !== false) {
			$decoded = json_decode($this->response);
			if ($decoded instanceof stdClass) {
				$this->response = $decoded;
			}
		}
	}

	/**
	 * Parse the response data
	 *
	 * @param stdClass $response
	 *
	 * @return stdClass
	 */
	private function parseResponse(stdClass $response): stdClass
	{
		$return = new stdClass();
		foreach (get_object_vars($response) as $key => $value) {
			if ($key === "result") continue;
			$return->{$key} = $value;
		}
		return $return;
	}
}