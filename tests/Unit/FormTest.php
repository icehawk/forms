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

namespace IceHawk\Forms\Tests\Unit;

use IceHawk\Forms\Exceptions\TokenHasExpired;
use IceHawk\Forms\Exceptions\TokenMismatch;
use IceHawk\Forms\Feedback;
use IceHawk\Forms\Form;
use IceHawk\Forms\Interfaces\IdentifiesForm;
use IceHawk\Forms\Interfaces\IdentifiesFormRequestSource;
use IceHawk\Forms\Interfaces\ProvidesFeedback;
use IceHawk\Forms\Security\Token;

class FormTest extends \PHPUnit_Framework_TestCase
{
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

	public function testHasDefaultTokenAfterConstruction()
	{
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$this->assertInstanceOf( Token::class, $form->getToken() );
		$this->assertInstanceOf( IdentifiesFormRequestSource::class, $form->getToken() );
		$this->assertNotEmpty( $form->getToken()->toString() );
	}

	public function testCanRenewToken()
	{
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
		$this->assertInstanceOf( IdentifiesFormRequestSource::class, $form->getToken() );
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

	public function testCanCheckIfTokenIsValid()
	{
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$initialToken = $form->getToken();

		$this->assertTrue( $form->isTokenValid( $initialToken ) );

		$form->renewToken();

		$this->assertFalse( $form->isTokenValid( $initialToken ) );
	}

	public function testThrowsExceptionWhenGuardingTokenStringValidity()
	{
		$this->setExpectedExceptionRegExp( TokenMismatch::class );

		$formId       = $this->getFormIdMock();
		$form         = new Form( $formId );
		$initialToken = $form->getToken();

		$form->guardTokenIsValid( $initialToken );

		$form->renewToken();

		$form->guardTokenIsValid( $initialToken );
	}

	public function testThrowsExceptionWhenGuardingTokenExpiry()
	{
		$this->setExpectedExceptionRegExp( TokenHasExpired::class );

		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$form->renewToken( (new Token())->expiresIn( 1 ) );
		$token = $form->getToken();

		$form->guardTokenIsValid( $token );

		sleep( 2 );

		$form->guardTokenIsValid( $token );
	}

	public function testCanCheckIfTokenHasExpired()
	{
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$form->renewToken( (new Token())->expiresIn( 1 ) );

		$this->assertFalse( $form->hasTokenExpired() );

		sleep( 2 );

		$this->assertTrue( $form->hasTokenExpired() );
	}

	public function testFormDataIsEmptyArrayAfterConstruction()
	{
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$this->assertInternalType( 'array', $form->getData() );
		$this->assertEmpty( $form->getData() );
	}

	public function testFormFeedbackIsEmptyArrayAfterConstruction()
	{
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$this->assertInternalType( 'array', $form->getFeedbacks() );
		$this->assertEmpty( $form->getFeedbacks() );
		$this->assertFalse( $form->hasFeedbacks() );
	}

	public function testCanCheckForFeedbackAtKey()
	{
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

	public function testCanGetSingleFeedback()
	{
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$feedback = $this->getFeedbackMock( 'test-message', 'warning' );
		$form->addFeedback( 'test-key', $feedback );

		$this->assertEquals( $feedback, $form->getFeedback( 'test-key' ) );
	}

	public function testCanGetEmptyFeedbackIfKeyIsNotSet()
	{
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$expectedFeedback = new Feedback( '', '' );

		$this->assertEquals( $expectedFeedback, $form->getFeedback( 'test-key' ) );
	}

	public function testCanGetAllFeedbacks()
	{
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$feedback1 = $this->getFeedbackMock( 'test-message1', 'error' );
		$feedback2 = $this->getFeedbackMock( 'test-message2', 'warning' );

		$expectedFeedbacks = [
			'test-key1' => $feedback1,
			'test-key2' => $feedback2,
		];

		$form->addFeedback( 'test-key1', $feedback1 );
		$form->addFeedback( 'test-key2', $feedback2 );

		$this->assertEquals( $expectedFeedbacks, $form->getFeedbacks() );
	}

	public function testCanGetFeedbacksFilteredByKey()
	{
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$feedback1 = $this->getFeedbackMock( 'test-message1', 'error' );
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
					return ($key == 'test-key2');
				}
			)
		);
	}

	public function testCanGetFeedbacksFilteredBySeverity()
	{
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$feedback1 = $this->getFeedbackMock( 'test-message1', 'error' );
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
					return ($feedback->getSeverity() == 'error');
				}
			)
		);
	}

	public function testCanAddMultipleFeedbacks()
	{
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

	public function testCheckIfDataWasSet()
	{
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

	public function testCanGetNullForNotSetDataKey()
	{
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$this->assertNull( $form->get( 'test-key' ) );
	}

	public function testCanGetValueForKey()
	{
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$form->set( 'test-key', 'test-value' );

		$this->assertEquals( 'test-value', $form->get( 'test-key' ) );
	}

	public function testCanUnsetDataForKey()
	{
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$form->set( 'test-key', 'test-value' );

		$this->assertEquals( 'test-value', $form->get( 'test-key' ) );

		$form->unset( 'test-key' );

		$this->assertNull( $form->get( 'test-key' ) );
	}

	public function testCanResetForm()
	{
		$formId       = $this->getFormIdMock();
		$form         = new Form( $formId );
		$initialToken = $form->getToken();

		$form->set( 'test-key', 'test-value' );
		$form->addFeedback( 'test-key', $this->getFeedbackMock( 'test-message', 'error' ) );

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

	public function testCanSerializeFormAsJson()
	{
		$formId = $this->getFormIdMock();
		$form   = new Form( $formId );

		$form->set( 'test-key', 'test-value' );
		$form->addFeedback( 'test-key', $this->getFeedbackMock( 'test-message', 'error' ) );

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
