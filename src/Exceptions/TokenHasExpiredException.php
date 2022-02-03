<?php declare(strict_types=1);

namespace IceHawk\Forms\Exceptions;

use RuntimeException;

final class TokenHasExpiredException extends RuntimeException
{
	public static function new() : self
	{
		return new self();
	}

	private function __construct()
	{
		parent::__construct( 'Token has expired' );
	}
}
