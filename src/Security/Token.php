<?php declare(strict_types=1);
/**
 * Copyright (c) 2016 Holger Woltersdorf & Contributors
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 */

namespace IceHawk\Forms\Security;

use DateTimeImmutable;
use Exception;
use IceHawk\Forms\Exceptions\InvalidExpiryInterval;
use IceHawk\Forms\Exceptions\InvalidTokenString;
use IceHawk\Forms\Interfaces\IdentifiesFormRequestSource;
use function base64_encode;
use function count;
use function random_bytes;

/**
 * Class Token
 * @package IceHawk\Forms\Security
 */
final class Token implements IdentifiesFormRequestSource
{
	const DELIMITER   = '[expiry]';

	const DATE_FORMAT = 'Y-m-d H:i:s';

	/** @var string */
	private $token;

	/** @var false|DateTimeImmutable */
	private $expiry;

	/**
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->token = base64_encode( random_bytes( 64 ) );
	}

	/**
	 * @param int $seconds
	 *
	 * @throws InvalidExpiryInterval
	 * @throws Exception
	 * @return Token
	 */
	public function expiresIn( int $seconds ) : Token
	{
		if ( $seconds > 0 )
		{
			$this->expiry = new DateTimeImmutable( sprintf( '+%d seconds', $seconds ) );

			return $this;
		}

		throw (new InvalidExpiryInterval())->withSeconds( $seconds );
	}

	public function toString() : string
	{
		if ( $this->expiry instanceof DateTimeImmutable )
		{
			$rawToken = $this->token . self::DELIMITER . $this->expiry->format( self::DATE_FORMAT );
		}
		else
		{
			$rawToken = $this->token;
		}

		return base64_encode( $rawToken );
	}

	public function __toString() : string
	{
		return $this->toString();
	}

	public function jsonSerialize()
	{
		return $this->toString();
	}

	public function equals( IdentifiesFormRequestSource $other ) : bool
	{
		if ( $other instanceof self )
		{
			return ($other->token === $this->token);
		}

		return false;
	}

	/**
	 * @throws Exception
	 * @return bool
	 */
	public function isExpired() : bool
	{
		if ( $this->expiry instanceof DateTimeImmutable )
		{
			return (new DateTimeImmutable() > $this->expiry);
		}

		return false;
	}

	/**
	 * @param string $tokenString
	 *
	 * @throws InvalidTokenString
	 * @throws Exception
	 * @return Token
	 */
	public static function fromString( string $tokenString ) : Token
	{
		$rawToken = base64_decode( $tokenString, true );

		if ( $rawToken !== false )
		{
			$parts = explode( self::DELIMITER, $rawToken );

			$token        = new self();
			$token->token = $parts[0];

			if ( count( $parts ) === 2 )
			{
				$token->expiry = DateTimeImmutable::createFromFormat( self::DATE_FORMAT, $parts[1] );
			}

			return $token;
		}

		throw (new InvalidTokenString())->withTokenString( $tokenString );
	}
}
