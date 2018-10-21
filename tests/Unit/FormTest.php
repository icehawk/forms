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

namespace IceHawk\Forms\Tests\Unit;

use IceHawk\Forms\Exceptions\TokenHasExpired;
use IceHawk\Forms\Exceptions\TokenMismatch;
use IceHawk\Forms\Feedback;
use IceHawk\Forms\Form;
use IceHawk\Forms\Interfaces\IdentifiesForm;
use IceHawk\Forms\Interfaces\IdentifiesFormRequestSource;
use IceHawk\Forms\Interfaces\ProvidesFeedback;
use IceHawk\Forms\Security\Token;
use PHPUnit\Framework\TestCase;
use function json_encode;

class FormTest extends TestCase
{
	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanConstructWithFormId()
	{
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$this->assertEquals( $formId, $form->getFormId() );
	}

	private function getFormIdMock() : \PHPUnit_Framework_MockObject_MockObject
	{
		$formId = $this->getMockBuilder( IdentifiesForm::class )->getMock();
		$formId->method( 'toString' )->willReturn( 'test-form-id' );
		$formId->method( '__toString' )->willReturn( 'test-form-id' );
		$formId->method( 'jsonSerialize' )->willReturn( '"test-form-id"' );

		return $formId;
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testHasDefaultTokenAfterConstruction()
	{
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$this->assertInstanceOf( Token::class, $form->getToken() );
		$this->assertNotEmpty( $form->getToken()->toString() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanRenewToken()
	{
		/** @var IdentifiesForm $formId */
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$initialToken = $form->getToken();

		$form->renewToken();

		$this->assertInstanceOf( Token::class, $form->getToken() );
		$this->assertFalse( $form->getToken()->equals( $initialToken ) );

		$currentToken = $form->getToken();

		$userDefinedToken = $this->getTokenMock();
		$form->renewToken( $userDefinedToken );

		$this->assertNotInstanceOf( Token::class, $form->getToken() );
		$this->assertFalse( $form->getToken()->equals( $initialToken ) );
		$this->assertFalse( $form->getToken()->equals( $currentToken ) );
	}

	private function getTokenMock() : \PHPUnit_Framework_MockObject_MockObject
	{
		$token = $this->getMockBuilder( IdentifiesFormRequestSource::class )->getMock();
		$token->method( 'toString' )->willReturn( 'test-token-string' );
		$token->method( '__toString' )->willReturn( 'test-token-string' );
		$token->method( 'isExpired' )->willReturn( false );
		$token->method( 'jsonSerialize' )->willReturn( '"test-token-string"' );

		return $token;
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanCheckIfTokenIsValid()
	{
		/** @var IdentifiesForm $formId */
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$initialToken = $form->getToken();

		$this->assertTrue( $form->isTokenValid( $initialToken ) );

		$form->renewToken();

		$this->assertFalse( $form->isTokenValid( $initialToken ) );
	}

	/**
	 * @throws TokenHasExpired
	 * @throws TokenMismatch
	 * @throws \PHPUnit\Framework\AssertionFailedError
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testThrowsExceptionWhenGuardingTokenStringValidity()
	{
		/** @var IdentifiesForm $formId */
		$formId       = $this->getFormIdMock();
		$form         = new Form( $formId );
		$initialToken = $form->getToken();

		$form->guardTokenIsValid( $initialToken );

		$form->renewToken();

		try
		{
			$form->guardTokenIsValid( $initialToken );
			$this->fail( 'Expected TokenMismatch exception to be thrown.' );
		}
		catch ( TokenMismatch $e )
		{
			$this->assertSame( $form->getToken(), $e->getFormToken() );
			$this->assertSame( $initialToken, $e->getCheckToken() );
		}
	}

	/**
	 * @throws TokenHasExpired
	 * @throws TokenMismatch
	 * @throws \IceHawk\Forms\Exceptions\InvalidExpiryInterval
	 */
	public function testThrowsExceptionWhenGuardingTokenExpiry()
	{
		$this->expectException( TokenHasExpired::class );

		/** @var IdentifiesForm $formId */
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$form->renewToken( (new Token())->expiresIn( 1 ) );
		$token = $form->getToken();

		$form->guardTokenIsValid( $token );

		sleep( 2 );

		$form->guardTokenIsValid( $token );
	}

	/**
	 * @throws \IceHawk\Forms\Exceptions\InvalidExpiryInterval
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanCheckIfTokenHasExpired()
	{
		/** @var IdentifiesForm $formId */
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$form->renewToken( (new Token())->expiresIn( 1 ) );

		$this->assertFalse( $form->hasTokenExpired() );

		sleep( 2 );

		$this->assertTrue( $form->hasTokenExpired() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testFormDataIsEmptyArrayAfterConstruction()
	{
		/** @var IdentifiesForm $formId */
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$this->assertInternalType( 'array', $form->getData() );
		$this->assertEmpty( $form->getData() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testFormFeedbackIsEmptyArrayAfterConstruction()
	{
		/** @var IdentifiesForm $formId */
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$this->assertInternalType( 'array', $form->getFeedbacks() );
		$this->assertEmpty( $form->getFeedbacks() );
		$this->assertFalse( $form->hasFeedbacks() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanCheckForFeedbackAtKey()
	{
		/** @var IdentifiesForm $formId */
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$feedback = $this->getFeedbackMock( 'test-message', 'warning' );
		$form->addFeedback( 'test-key', $feedback );

		$this->assertTrue( $form->hasFeedbacks() );
		$this->assertTrue( $form->hasFeedback( 'test-key' ) );
		$this->assertFalse( $form->hasFeedback( 'other-key' ) );
	}

	private function getFeedbackMock( string $message, string $severity ) : \PHPUnit_Framework_MockObject_MockObject
	{
		$feedback = $this->getMockBuilder( ProvidesFeedback::class )->getMock();
		$feedback->method( 'getMessage' )->willReturn( $message );
		$feedback->method( 'getSeverity' )->willReturn( $severity );

		return $feedback;
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetSingleFeedback()
	{
		/** @var IdentifiesForm $formId */
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$feedback = $this->getFeedbackMock( 'test-message', 'warning' );
		$form->addFeedback( 'test-key', $feedback );

		$this->assertEquals( $feedback, $form->getFeedback( 'test-key' ) );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetEmptyFeedbackIfKeyIsNotSet()
	{
		/** @var IdentifiesForm $formId */
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$expectedFeedback = new Feedback( '', '' );

		$this->assertEquals( $expectedFeedback, $form->getFeedback( 'test-key' ) );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetAllFeedbacks()
	{
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		/** @var ProvidesFeedback $feedback1 */
		$feedback1 = $this->getFeedbackMock( 'test-message1', 'error' );
		/** @var ProvidesFeedback $feedback2 */
		$feedback2 = $this->getFeedbackMock( 'test-message2', 'warning' );

		$expectedFeedbacks = [
			'test-key1' => $feedback1,
			'test-key2' => $feedback2,
		];

		$form->addFeedback( 'test-key1', $feedback1 );
		$form->addFeedback( 'test-key2', $feedback2 );

		$this->assertEquals( $expectedFeedbacks, $form->getFeedbacks() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetFeedbacksFilteredByKey()
	{
		/** @var IdentifiesForm $formId */
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		/** @var ProvidesFeedback $feedback1 */
		$feedback1 = $this->getFeedbackMock( 'test-message1', 'error' );
		/** @var ProvidesFeedback $feedback2 */
		$feedback2 = $this->getFeedbackMock( 'test-message2', 'warning' );

		$expectedFeedbacks = [
			'test-key2' => $feedback2,
		];

		$form->addFeedback( 'test-key1', $feedback1 );
		$form->addFeedback( 'test-key2', $feedback2 );

		$this->assertEquals(
			$expectedFeedbacks,
			$form->getFeedbacks(
				function ( ProvidesFeedback $feedback, string $key )
				{
					return ($key === 'test-key2');
				}
			)
		);
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetFeedbacksFilteredBySeverity()
	{
		/** @var IdentifiesForm $formId */
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		/** @var ProvidesFeedback $feedback1 */
		$feedback1 = $this->getFeedbackMock( 'test-message1', 'error' );
		/** @var ProvidesFeedback $feedback2 */
		$feedback2 = $this->getFeedbackMock( 'test-message2', 'warning' );

		$expectedFeedbacks = [
			'test-key1' => $feedback1,
		];

		$form->addFeedback( 'test-key1', $feedback1 );
		$form->addFeedback( 'test-key2', $feedback2 );

		$this->assertEquals(
			$expectedFeedbacks,
			$form->getFeedbacks(
				function ( ProvidesFeedback $feedback )
				{
					return ($feedback->getSeverity() === 'error');
				}
			)
		);
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanAddMultipleFeedbacks()
	{
		/** @var IdentifiesForm $formId */
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$feedback1 = $this->getFeedbackMock( 'test-message1', 'error' );
		$feedback2 = $this->getFeedbackMock( 'test-message2', 'warning' );

		$form->addFeedbacks(
			[
				'test-key1' => $feedback1,
				'test-key2' => $feedback2,
			]
		);

		$this->assertTrue( $form->hasFeedback( 'test-key1' ) );
		$this->assertTrue( $form->hasFeedback( 'test-key2' ) );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCheckIfDataWasSet()
	{
		/** @var IdentifiesForm $formId */
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$this->assertFalse( $form->wasDataSet() );

		$form->setData( [] );

		$this->assertTrue( $form->wasDataSet() );

		$form->reset();

		$this->assertFalse( $form->wasDataSet() );

		$form->set( 'test-key', 'test-data' );

		$this->assertTrue( $form->wasDataSet() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetNullForNotSetDataKey()
	{
		/** @var IdentifiesForm $formId */
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$this->assertNull( $form->get( 'test-key' ) );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetValueForKey()
	{
		/** @var IdentifiesForm $formId */
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$form->set( 'test-key', 'test-value' );

		$this->assertEquals( 'test-value', $form->get( 'test-key' ) );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanUnsetDataForKey()
	{
		/** @var IdentifiesForm $formId */
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$form->set( 'test-key', 'test-value' );

		$this->assertEquals( 'test-value', $form->get( 'test-key' ) );

		$form->unset( 'test-key' );

		$this->assertNull( $form->get( 'test-key' ) );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanResetForm()
	{
		/** @var IdentifiesForm $formId */
		$formId       = $this->getFormIdMock();
		$form         = new Form( $formId );
		$initialToken = $form->getToken();

		/** @var ProvidesFeedback $feedback */
		$feedback = $this->getFeedbackMock( 'test-message', 'error' );

		$form->set( 'test-key', 'test-value' );
		$form->addFeedback( 'test-key', $feedback );

		$this->assertTrue( $form->wasDataSet() );
		$this->assertTrue( $form->isset( 'test-key' ) );
		$this->assertTrue( $form->hasFeedback( 'test-key' ) );
		$this->assertTrue( $form->getToken()->equals( $initialToken ) );

		$form->reset();

		$this->assertFalse( $form->wasDataSet() );
		$this->assertFalse( $form->isset( 'test-key' ) );
		$this->assertFalse( $form->hasFeedback( 'test-key' ) );
		$this->assertFalse( $form->getToken()->equals( $initialToken ) );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanSerializeFormAsJson()
	{
		/** @var IdentifiesForm $formId */
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		/** @var ProvidesFeedback $feedback */
		$feedback = $this->getFeedbackMock( 'test-message', 'error' );

		$form->set( 'test-key', 'test-value' );
		$form->addFeedback( 'test-key', $feedback );

		$formJson     = json_encode( $form );
		$expectedJson = json_encode(
			[
				'formId'     => $form->getFormId(),
				'token'      => $form->getToken(),
				'data'       => $form->getData(),
				'feedbacks'  => $form->getFeedbacks(),
				'dataWasSet' => $form->wasDataSet(),
			]
		);

		$this->assertJson( $formJson );
		$this->assertEquals( $expectedJson, $formJson );
	}
}
