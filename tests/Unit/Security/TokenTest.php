<?php declare(strict_types = 1);
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

namespace IceHawk\Forms\Tests\Unit\Security;

use IceHawk\Forms\Interfaces\IdentifiesFormRequestSource;
use IceHawk\Forms\Security\Token;

class TokenTest extends \PHPUnit_Framework_TestCase
{
	public function testTokenEqualsOther()
	{
		$token = new Token();
		$other = Token::fromString( $token->toString() );

		$this->assertTrue( $token->equals( $other ) );
	}

	public function testTokenEqualsOtherWithExpiry()
	{
		$token = (new Token())->expiresIn( 2 );
		$other = Token::fromString( $token->toString() );

		$this->assertTrue( $token->equals( $other ) );
	}

	public function testTokenNotEqualsOtherClass()
	{
		$token = Token::fromString( '123' );
		$other = new class implements IdentifiesFormRequestSource
		{
			public function toString() : string
			{
				return '456';
			}

			public function __toString() : string
			{
				return $this->toString();
			}

			public function equals( IdentifiesFormRequestSource $other ) : bool
			{
				return ($other->toString() == $this->toString());
			}

			public function isExpired() : bool
			{
				return false;
			}

			public function jsonSerialize()
			{
				return $this->toString();
			}
		};

		$this->assertFalse( $token->equals( $other ) );
		$this->assertFalse( $other->equals( $token ) );
	}

	public function testTokenExpires()
	{
		$token = (new Token())->expiresIn( 1 );
		$other = Token::fromString( $token->toString() );

		$this->assertFalse( $token->isExpired() );
		$this->assertFalse( $other->isExpired() );

		sleep( 2 );

		$this->assertTrue( $token->isExpired() );
		$this->assertTrue( $other->isExpired() );
	}

	/**
	 * @expectedException \IceHawk\Forms\Exceptions\InvalidExpiryInterval
	 */
	public function testInvalidExpiryIntervalThrowsException()
	{
		(new Token())->expiresIn( -2 );
	}

	/**
	 * @expectedException \IceHawk\Forms\Exceptions\InvalidTokenString
	 */
	public function testInvalidTokenStringThrowsException()
	{
		Token::fromString( 'Invalid•String' );
	}

	public function testCanGetTokenAsString()
	{
		$token = new Token();

		$this->assertInternalType( 'string', (string)$token );
		$this->assertEquals( $token->toString(), (string)$token );
		$this->assertEquals( json_encode( $token->toString() ), json_encode( $token ) );
	}
}
