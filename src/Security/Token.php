<?php declare(strict_types=1);

namespace IceHawk\Forms\Security;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use IceHawk\Forms\Exceptions\InvalidExpiryIntervalException;
use IceHawk\Forms\Exceptions\InvalidTokenStringException;
use IceHawk\Forms\Interfaces\TokenInterface;
use function base64_encode;
use function count;
use function random_bytes;
use function sprintf;

final class Token implements TokenInterface
{
	private const BYTE_LENGTH = 64;

	private const DELIMITER   = '[expiry]';

	private const DATE_FORMAT = 'Y-m-d H:i:s';

	/**
	 * @throws Exception
	 */
	public static function new() : self
	{
		return new self( base64_encode( random_bytes( self::BYTE_LENGTH ) ) );
	}

	/**
	 * @param int $seconds
	 *
	 * @return Token
	 * @throws InvalidExpiryIntervalException
	 * @throws Exception
	 */
	public static function newWithExpiry( int $seconds ) : self
	{
		if ( $seconds < 1 )
		{
			throw InvalidExpiryIntervalException::withSeconds( $seconds );
		}

		$expiry = new DateTimeImmutable( sprintf( '+%d seconds', $seconds ) );

		return new self( base64_encode( random_bytes( self::BYTE_LENGTH ) ), $expiry );
	}

	private function __construct( private string $token, private ?DateTimeInterface $expiry = null ) { }

	public function toString() : string
	{
		$rawToken = $this->token;

		if ( null !== $this->expiry )
		{
			$rawToken = $this->token . self::DELIMITER . $this->expiry->format( self::DATE_FORMAT );
		}

		return base64_encode( $rawToken );
	}

	public function __toString() : string
	{
		return $this->toString();
	}

	public function jsonSerialize() : string
	{
		return $this->toString();
	}

	public function equals( TokenInterface $other ) : bool
	{
		return (string)$other === $this->toString();
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function isExpired() : bool
	{
		if ( $this->expiry instanceof DateTimeInterface )
		{
			/** @noinspection InsufficientTypesControlInspection */
			return (new DateTimeImmutable() > $this->expiry);
		}

		return false;
	}

	/**
	 * @param string $tokenString
	 *
	 * @return Token
	 * @throws Exception
	 * @throws InvalidTokenStringException
	 */
	public static function fromString( string $tokenString ) : self
	{
		$rawToken = base64_decode( $tokenString, true );

		if ( false === $rawToken )
		{
			throw InvalidTokenStringException::withTokenString( $tokenString );
		}

		$parts = explode( self::DELIMITER, $rawToken );

		if ( count( $parts ) === 1 )
		{
			return new self( $parts[0] );
		}

		$expiry = DateTimeImmutable::createFromFormat( self::DATE_FORMAT, $parts[1] );

		if ( false === $expiry )
		{
			throw InvalidTokenStringException::withTokenString( $tokenString );
		}

		return new self( $parts[0], $expiry );
	}
}
