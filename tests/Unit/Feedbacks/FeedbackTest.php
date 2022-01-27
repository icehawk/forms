<?php declare(strict_types=1);

namespace IceHawk\Forms\Tests\Unit\Feedbacks;

use IceHawk\Forms\Feedbacks\Feedback;
use JsonException;
use PHPUnit\Framework\TestCase;
use function json_encode;

final class FeedbackTest extends TestCase
{
	public function testConstruction() : void
	{
		$feedback = Feedback::new( 'unit-test', 'Test-Message', 'high' );

		self::assertSame( 'unit-test', $feedback->getKey() );
		self::assertSame( 'Test-Message', $feedback->getMessage() );
		self::assertSame( 'high', $feedback->getSeverity() );
	}

	/**
	 * @throws JsonException
	 */
	public function testCanSerializeAsJson() : void
	{
		$feedback = Feedback::new( 'unit-test', 'Test-Message', 'high' );

		$expectedJson = json_encode(
			['key' => 'unit-test', 'message' => 'Test-Message', 'severity' => 'high'],
			JSON_THROW_ON_ERROR
		);

		$jsonFeedback = json_encode( $feedback, JSON_THROW_ON_ERROR );

		self::assertJson( $jsonFeedback );
		self::assertSame( $expectedJson, $jsonFeedback );
	}
}
