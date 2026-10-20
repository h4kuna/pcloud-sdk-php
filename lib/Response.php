<?php

declare(strict_types=1);

namespace pCloud\Sdk;

use stdClass;

/**
 * Class Response
 * The main purpose of this class is to organise the cURL response data into recognizable bundle
 */
final class Response
{

	/**
	 * Response data, decoded to stdClass if it is a valid JSON object
	 */
	private string|stdClass $response;

	private int $httpCode;

	private string $contentType;

	/**
	 * Class constructor
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