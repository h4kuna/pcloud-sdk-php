<?php declare(strict_types = 1);

namespace pCloud\Sdk\Tests;

use pCloud\Sdk\Curl;
use pCloud\Sdk\Exception;
use pCloud\Sdk\Response;
use stdClass;

/**
 * Curl replacement, which answers every request with an error response of the pCloud API
 */
final class ErrorCurl extends Curl
{

	/**
	 * @throws Exception
	 */
	public function exec(): stdClass
	{
		$response = new Response('{"result": 2094, "error": "Invalid \'access_token\' provided."}', 200, 'application/json');

		return $response->get();
	}

}
