<?php declare(strict_types=1);

namespace IceHawk\Forms\Exceptions;

use InvalidArgumentException;

final class InvalidFormIdException extends InvalidArgumentException
{
	public static function empty() : self
	{
		return new self( 'Invalid value for form ID: empty' );
	}

	private function __construct( string $message )
	{
		parent::__construct( $message );
	}
}