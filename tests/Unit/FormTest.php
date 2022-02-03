<?php declare(strict_types=1);

namespace IceHawk\Forms\Tests\Unit;

use Exception;
use IceHawk\Forms\Exceptions\TokenHasExpiredException;
use IceHawk\Forms\Exceptions\TokenMismatchException;
use IceHawk\Forms\Form;
use IceHawk\Forms\Interfaces\FeedbackInterface;
use IceHawk\Forms\Interfaces\FormIdInterface;
use IceHawk\Forms\Interfaces\TokenInterface;
use IceHawk\Forms\Security\Token;
use JsonException;
use PHPUnit\Framework\TestCase;
use function iterator_to_array;
use function json_encode;

final class FormTest extends TestCase
{
	/**
	 * @throws Exception
	 */
	public function testCanConstructWithFormId() : void
	{
		$formId = $this->getFormIdMock();
		$form   = Form::new( $formId );

		self::assertSame( $formId, $form->getFormId() );
	}

	private function getFormIdMock() : FormIdInterface
	{
		$formId = $this->getMockBuilder( FormIdInterface::class )->getMock();
		$formId->method( 'toString' )->willReturn( 'test-form-id' );
		$formId->method( '__toString' )->willReturn( 'test-form-id' );
		$formId->method( 'jsonSerialize' )->willReturn( '"test-form-id"' );

		return $formId;
	}

	/**
	 * @throws Exception
	 */
	public function testDefaults() : void
	{
		$form = Form::new( $this->getFormIdMock() );

		self::assertInstanceOf( Token::class, $form->getToken() );
		self::assertNotEmpty( $form->getToken()->toString() );
		self::assertCount( 0, $form->getFeedbacks() );
		self::assertFalse( $form->hasFeedbacks() );
		self::assertFalse( $form->wasDataSet() );
		self::assertSame( [], $form->getData() );
	}

	/**
	 * @throws Exception
	 */
	public function testCanRenewToken() : void
	{
		$form = Form::new( $this->getFormIdMock() );

		$initialToken = $form->getToken();

		$form->renewToken();

		self::assertInstanceOf( Token::class, $form->getToken() );
		self::assertFalse( $form->getToken()->equals( $initialToken ) );

		$currentToken = $form->getToken();

		$userDefinedToken = $this->getTokenMock();
		$form->renewToken( $userDefinedToken );

		self::assertNotInstanceOf( Token::class, $form->getToken() );
		self::assertFalse( $form->getToken()->equals( $initialToken ) );
		self::assertFalse( $form->getToken()->equals( $currentToken ) );
	}

	private function getTokenMock() : TokenInterface
	{
		$token = $this->getMockBuilder( TokenInterface::class )->getMock();
		$token->method( 'toString' )->willReturn( 'test-token-string' );
		$token->method( '__toString' )->willReturn( 'test-token-string' );
		$token->method( 'isExpired' )->willReturn( false );
		$token->method( 'jsonSerialize' )->willReturn( '"test-token-string"' );

		return $token;
	}

	/**
	 * @throws Exception
	 */
	public function testCanCheckIfTokenIsValid() : void
	{
		$form = Form::new( $this->getFormIdMock() );

		$initialToken = $form->getToken();

		self::assertTrue( $form->isTokenValid( $initialToken ) );

		$form->renewToken();

		self::assertFalse( $form->isTokenValid( $initialToken ) );
	}

	/**
	 * @throws Exception
	 */
	public function testGuardingTokenIsValid() : void
	{
		$form = Form::new( $this->getFormIdMock() );

		$initialToken = $form->getToken();

		$form->guardTokenIsValid( $initialToken );

		$form->renewToken();

		try
		{
			$form->guardTokenIsValid( $initialToken );

			$this->fail( 'Expected TokenMismatchException to be thrown.' );
		}
		catch ( TokenMismatchException $e )
		{
			self::assertSame( $form->getToken(), $e->getFormToken() );
			self::assertSame( $initialToken, $e->getCheckToken() );
		}
	}

	/**
	 * @throws Exception
	 */
	public function testGuardTokenWithExpiryIsValid() : void
	{
		$form = Form::new( $this->getFormIdMock() );
		$form->renewToken( Token::newWithExpiry( 1 ) );
		$token = $form->getToken();

		$form->guardTokenIsValid( $token );

		sleep( 2 );

		$this->expectException( TokenHasExpiredException::class );
		$this->expectExceptionMessage( 'Token has expired' );

		$form->guardTokenIsValid( $token );
	}

	/**
	 * @throws Exception
	 */
	public function testCanCheckIfTokenHasExpired() : void
	{
		$form = Form::new( $this->getFormIdMock() );
		$form->renewToken( Token::newWithExpiry( 1 ) );

		self::assertFalse( $form->hasTokenExpired() );

		sleep( 2 );

		self::assertTrue( $form->hasTokenExpired() );
	}

	/**
	 * @throws Exception
	 */
	public function testHasFeedbacks() : void
	{
		$form = Form::new( $this->getFormIdMock() );

		self::assertFalse( $form->hasFeedbacks() );

		$form->addFeedbacks(
			$this->getFeedbackMock( 'key1', 'test-message-1', 'low' ),
			$this->getFeedbackMock( 'key2', 'test-message-2', 'high' ),
		);

		self::assertTrue( $form->hasFeedbacks() );
	}

	private function getFeedbackMock( string $key, string $message, string $severity ) : FeedbackInterface
	{
		$feedback = $this->getMockBuilder( FeedbackInterface::class )->getMock();
		$feedback->method( 'getKey' )->willReturn( $key );
		$feedback->method( 'getMessage' )->willReturn( $message );
		$feedback->method( 'getSeverity' )->willReturn( $severity );

		return $feedback;
	}

	/**
	 * @throws Exception
	 */
	public function testCanGetAllFeedbacks() : void
	{
		$form = Form::new( $this->getFormIdMock() );

		self::assertCount( 0, $form->getFeedbacks() );

		$feedback1         = $this->getFeedbackMock( 'key1', 'test-message-1', 'low' );
		$feedback2         = $this->getFeedbackMock( 'key2', 'test-message-2', 'high' );
		$expectedFeedbacks = [$feedback1, $feedback2];

		$form->addFeedbacks( $feedback1, $feedback2 );

		self::assertCount( 2, $form->getFeedbacks() );

		self::assertSame( $expectedFeedbacks, iterator_to_array( $form->getFeedbacks(), false ) );
	}

	/**
	 * @throws Exception
	 */
	public function testWasDataSet() : void
	{
		$form = Form::new( $this->getFormIdMock() );

		self::assertFalse( $form->wasDataSet() );

		$form->setData( [] );

		self::assertTrue( $form->wasDataSet() );

		$form->reset();

		self::assertFalse( $form->wasDataSet() );

		$form->set( 'test-key', 'test-data' );

		self::assertTrue( $form->wasDataSet() );
	}

	/**
	 * @throws Exception
	 */
	public function testGet() : void
	{
		$form = Form::new( $this->getFormIdMock() );

		$form->setData( [
			'unit' => 'test',
			'test' => 'unit',
		] );

		self::assertNull( $form->get( 'not-existing-key' ) );

		self::assertSame( 'test', $form->get( 'unit' ) );
		self::assertSame( 'unit', $form->get( 'test' ) );
	}

	/**
	 * @throws Exception
	 */
	public function testUnset() : void
	{
		$form = Form::new( $this->getFormIdMock() );

		$form->setData( [
			'unit' => 'test',
			'test' => 'unit',
		] );

		self::assertNull( $form->get( 'not-existing-key' ) );

		self::assertSame( 'test', $form->get( 'unit' ) );
		self::assertSame( 'unit', $form->get( 'test' ) );

		$form->unset( 'unit' );

		self::assertNull( $form->get( 'not-existing-key' ) );

		self::assertSame( 'unit', $form->get( 'test' ) );
		self::assertNull( $form->get( 'unit' ) );
	}

	/**
	 * @throws Exception
	 */
	public function testReset() : void
	{
		$form = Form::new( $this->getFormIdMock() );

		$initialToken = $form->getToken();

		$form->set( 'test-key', 'test-value' );
		$form->addFeedbacks( $this->getFeedbackMock( 'test-key', 'test-message', 'error' ) );

		self::assertTrue( $form->wasDataSet() );
		self::assertTrue( $form->isset( 'test-key' ) );
		self::assertTrue( $form->hasFeedbacks() );
		self::assertTrue( $form->getToken()->equals( $initialToken ) );
		self::assertTrue( $form->isTokenValid( $initialToken ) );

		$form->reset();

		self::assertFalse( $form->wasDataSet() );
		self::assertFalse( $form->isset( 'test-key' ) );
		self::assertFalse( $form->hasFeedbacks() );
		self::assertFalse( $form->getToken()->equals( $initialToken ) );
		self::assertFalse( $form->isTokenValid( $initialToken ) );
	}

	/**
	 * @throws JsonException
	 * @throws Exception
	 */
	public function testJsonSerialize() : void
	{
		$form = Form::new( $this->getFormIdMock() );

		$form->setData(
			[
				'key-test' => 'some-value',
				'foo'      => 123,
			]
		);

		$form->set( 'test-key', 'test-value' );

		$form->addFeedbacks(
			$this->getFeedbackMock( 'test-key', 'test-message 1', 'error' ),
			$this->getFeedbackMock( 'test-key', 'test-message 2', 'warning' ),
		);

		$formJson     = json_encode( $form, JSON_THROW_ON_ERROR );
		$expectedJson = json_encode(
			[
				'formId'     => $form->getFormId(),
				'token'      => $form->getToken(),
				'data'       => $form->getData(),
				'feedbacks'  => $form->getFeedbacks(),
				'dataWasSet' => $form->wasDataSet(),
			],
			JSON_THROW_ON_ERROR
		);

		self::assertJson( $formJson );
		self::assertEquals( $expectedJson, $formJson );
	}
}
