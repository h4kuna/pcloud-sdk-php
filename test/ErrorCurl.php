<?php

declare(strict_types=1);

use pCloud\Sdk\Curl;
use pCloud\Sdk\Response;

/**
 * Curl replacement, which answers every request with an error response of the pCloud API
 */
final class ErrorCurl extends Curl
{

	/**
	 * @return stdClass
	 * @throws \pCloud\Sdk\Exception
	 */
	public function exec(): stdClass
	{
		$response = new Response('{"result": 2094, "error": "Invalid \'access_token\' provided."}', 200, "application/json");

		return $response->get();
	}
}
