<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms\Security;

use IceHawk\Forms\Exceptions\InvalidExpiryInterval;
use IceHawk\Forms\Exceptions\InvalidTokenString;
use IceHawk\Forms\Interfaces\IdentifiesFormRequestSource;

/**
 * Class Token
 * @package IceHawk\Forms\Security
 */
final class Token implements IdentifiesFormRequestSource
{
	const DELIMITER   = '//';

	const DATE_FORMAT = 'Y-m-d H:i:s';

	/** @var string */
	private $token;

	/** @var \DateTimeImmutable */
	private $expiry;

	public function __construct()
	{
		$this->token = base64_encode( random_bytes( 64 ) );
	}

	/**
	 * @param int $seconds
	 *
	 * @throws InvalidExpiryInterval
	 * @return Token
	 */
	public function expiresIn( int $seconds ) : self
	{
		if ( $seconds > 0 )
		{
			$this->expiry = new \DateTimeImmutable( sprintf( '+%d seconds', $seconds ) );

			return $this;
		}

		throw (new InvalidExpiryInterval())->withSeconds( $seconds );
	}

	/**
	 * @return string
	 */
	public function toString() : string
	{
		if ( $this->expiry instanceof \DateTimeImmutable )
		{
			$rawToken = $this->token . self::DELIMITER . $this->expiry->format( self::DATE_FORMAT );
		}
		else
		{
			$rawToken = $this->token;
		}

		return base64_encode( $rawToken );
	}

	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->toString();
	}

	/**
	 * @return string
	 */
	public function jsonSerialize()
	{
		return $this->toString();
	}

	public function equals( IdentifiesFormRequestSource $other ) : bool
	{
		if ( $other instanceof Token )
		{
			return ($other->token == $this->token);
		}

		return false;
	}

	public function isExpired() : bool
	{
		if ( $this->expiry instanceof \DateTimeImmutable )
		{
			return (new \DateTimeImmutable() > $this->expiry);
		}

		return false;
	}

	/**
	 * @param string $tokenString
	 *
	 * @throws InvalidTokenString
	 * @return Token
	 */
	public static function fromString( string $tokenString ) : self
	{
		$rawToken = base64_decode( $tokenString, true );

		if ( $rawToken !== false )
		{
			$parts = explode( self::DELIMITER, $rawToken );

			$token        = new self();
			$token->token = $parts[0];

			if ( count( $parts ) == 2 )
			{
				$token->expiry = \DateTimeImmutable::createFromFormat( self::DATE_FORMAT, $parts[1] );
			}

			return $token;
		}

		throw (new InvalidTokenString())->withTokenString( $tokenString );
	}
}