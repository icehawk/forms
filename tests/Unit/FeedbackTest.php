<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms\Tests\Unit;

use IceHawk\Forms\Feedback;

class FeedbackTest extends \PHPUnit_Framework_TestCase
{
	public function testCanConstructWithMessage()
	{
		$feedback = new Feedback( 'Test-Message' );

		$this->assertEquals( 'Test-Message', $feedback->getMessage() );
		$this->assertEquals( Feedback::ERROR, $feedback->getSeverity() );
	}

	public function testCanConstructWithMessageAndSeverity()
	{
		$feedback = new Feedback( 'Test-Message', Feedback::NOTICE );

		$this->assertEquals( 'Test-Message', $feedback->getMessage() );
		$this->assertEquals( Feedback::NOTICE, $feedback->getSeverity() );
	}

	public function testCanSerializeAsJson()
	{
		$feedback     = new Feedback( 'Test-Message', Feedback::WARNING );
		$expectedJson = json_encode( [ 'message' => 'Test-Message', 'severity' => 'warning' ] );

		$jsonFeedback = json_encode( $feedback );

		$this->assertJson( $jsonFeedback );
		$this->assertEquals( $expectedJson, $jsonFeedback );
	}
}
