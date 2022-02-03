<?php declare(strict_types=1);

namespace IceHawk\Forms\Exceptions;

use InvalidArgumentException;

final class InvalidTokenStringException extends InvalidArgumentException
{
	private string $tokenString = '';

	public static function withTokenString( string $tokenString ) : self
	{
		$instance              = new self( sprintf( 'Invalid token string: %s', $tokenString ) );
		$instance->tokenString = $tokenString;

		return $instance;
	}

	private function __construct( string $message )
	{
		parent::__construct( $message );
	}

	public function getTokenString() : string
	{
		return $this->tokenString;
	}
}
