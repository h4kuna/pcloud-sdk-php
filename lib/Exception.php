<?php declare(strict_types = 1);

namespace pCloud\Sdk;

use Exception as BaseException;
use Throwable;

/**
 * Local Exception handler
 * Overwrites the error code to 5000+
 */
final class Exception extends BaseException
{

	public function __construct(
		string $message,
		?Throwable $cause = null,
	)
	{
		parent::__construct($message, 5000, $cause);
	}

}
