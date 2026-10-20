<?php

/**
 * Assertions on the responses produced by pCloud\Sdk\TestCurl
 */
trait AssertsRequestUrl
{

	/**
	 * @param string $expected
	 * @param mixed $response
	 *
	 * @return void
	 */
	private static function assertRequestUrl(string $expected, $response): void
	{
		self::assertInstanceOf(stdClass::class, $response);
		self::assertSame($expected, $response->url);
	}
}
