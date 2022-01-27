<?php declare(strict_types=1);

namespace IceHawk\Forms\Tests\Unit\Security;

use Exception;
use IceHawk\Forms\Exceptions\InvalidExpiryIntervalException;
use IceHawk\Forms\Exceptions\InvalidTokenStringException;
use IceHawk\Forms\Security\Token;
use JsonException;
use PHPUnit\Framework\TestCase;
use function json_encode;
use const JSON_THROW_ON_ERROR;

final class TokenTest extends TestCase
{
	/**
	 * @throws Exception
	 */
	public function testTokenEqualsOther() : void
	{
		$token = Token::new();
		$other = Token::fromString( $token->toString() );

		self::assertTrue( $token->equals( $other ) );
		self::assertTrue( $other->equals( $token ) );
	}

	/**
	 * @throws Exception
	 */
	public function testTokenEqualsOtherWithExpiry() : void
	{
		$token = Token::newWithExpiry( 2 );
		$other = Token::fromString( $token->toString() );

		self::assertTrue( $token->equals( $other ) );
		self::assertTrue( $other->equals( $token ) );
	}

	/**
	 * @throws Exception
	 */
	public function testTokenExpires() : void
	{
		$token = Token::newWithExpiry( 1 );
		$other = Token::fromString( $token->toString() );

		self::assertFalse( $token->isExpired() );
		self::assertFalse( $other->isExpired() );

		sleep( 2 );

		self::assertTrue( $token->isExpired() );
		self::assertTrue( $other->isExpired() );
	}

	/**
	 * @throws Exception
	 */
	public function testInvalidExpiryIntervalThrowsException() : void
	{
		$this->expectException( InvalidExpiryIntervalException::class );
		$this->expectExceptionMessage( 'Invalid expiry interval: -2 seconds' );

		Token::newWithExpiry( -2 );
	}

	/**
	 * @throws Exception
	 */
	public function testInvalidTokenStringThrowsException() : void
	{
		$this->expectException( InvalidTokenStringException::class );
		$this->expectExceptionMessage( 'Invalid token string: Invalid•String' );

		Token::fromString( 'Invalid•String' );
	}

	public function testCanGetTokenAsString() : void
	{
		$token          = Token::new();
		$expectedString = $token->toString();

		self::assertSame( $expectedString, (string)$token );
	}

	/**
	 * @throws JsonException
	 * @throws Exception
	 */
	public function testCanJsonEncodeToken() : void
	{
		$token        = Token::new();
		$expectedJson = json_encode( $token->toString(), JSON_THROW_ON_ERROR );

		self::assertJsonStringEqualsJsonString( $expectedJson, json_encode( $token, JSON_THROW_ON_ERROR ) );
	}
}
