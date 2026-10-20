<?php declare(strict_types = 1);

namespace pCloud\Sdk\Tests;

use stdClass;

/**
 * Assertions on the responses produced by pCloud\Sdk\TestCurl
 */
trait AssertsRequestUrl
{

	private static function assertRequestUrl(
		string $expected,
		mixed $response,
	): void
	{
		self::assertInstanceOf(stdClass::class, $response);
		self::assertSame($expected, $response->url);
	}

}
