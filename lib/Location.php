<?php declare(strict_types = 1);

namespace pCloud\Sdk;

use function intval;

/**
 * pCloud data center location, the value is the API host
 */
enum Location: string
{

	case Us = 'https://api.pcloud.com/';
	case Eu = 'https://eapi.pcloud.com/';

	/**
	 * Creates the location from the location ID returned by pCloud (1 - USA, 2 - EU),
	 * unknown IDs fall back to the USA
	 */
	public static function fromId(string|int $locationId): self
	{
		return match (intval($locationId)) {
			2 => self::Eu,
			1 => self::Us,
			default => throw new LogicException('Invalid location id'),
		};
	}

}
