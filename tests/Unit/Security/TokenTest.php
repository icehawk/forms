<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms\Tests\Unit\Security;

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
