<?php declare(strict_types=1);

namespace IceHawk\Forms\Exceptions;

use InvalidArgumentException;

final class InvalidExpiryIntervalException extends InvalidArgumentException
{
	private int $seconds = 0;

	public static function withSeconds( int $seconds ) : self
	{
		$instance          = new self( sprintf( 'Invalid expiry interval: %d seconds', $seconds ) );
		$instance->seconds = $seconds;

		return $instance;
	}

	private function __construct( string $message )
	{
		parent::__construct( $message );
	}

	public function getSeconds() : int
	{
		return $this->seconds;
	}
}
