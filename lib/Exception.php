<?php

namespace pCloud\Sdk;

use Throwable;

/**
 * Local Exception handler
 * Overwrites the error code to 5000+
 *
 * @package pCloud\Sdk
 */
class Exception extends \Exception
{

	/**
	 * Class constructor
	 *
	 * @param string $message
	 * @param Throwable|null $cause
	 */
	function __construct(string $message, ?Throwable $cause = null)
	{
		parent::__construct($message, 5000, $cause);
	}
}
