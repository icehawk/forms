<?php declare(strict_types=1);

namespace IceHawk\Forms\Tests\Unit\Feedbacks;

use IceHawk\Forms\Feedbacks\Feedback;
use IceHawk\Forms\Feedbacks\Feedbacks;
use IceHawk\Forms\Interfaces\FeedbackCollectionInterface;
use IceHawk\Forms\Interfaces\FeedbackInterface;
use JsonException;
use PHPUnit\Framework\TestCase;
use function json_encode;
use const JSON_THROW_ON_ERROR;

final class FeedbacksTest extends TestCase
{
	private FeedbackCollectionInterface $feedbacks;

	protected function setUp() : void
	{
		$this->feedbacks = Feedbacks::new(
			Feedback::new( 'unit', 'Unit-Message 1', 'low' ),
			Feedback::new( 'unit', 'Unit-Message 2', 'low' ),
			Feedback::new( 'test', 'Test-Message 3', 'high' ),
			Feedback::new( 'test', 'Test-Message 4', 'high' ),
			Feedback::new( 'test', 'Test-Message 5', 'normal' ),
		);
	}

	public function testGet() : void
	{
		self::assertCount( 2, $this->feedbacks->get( 'unit' ) );
		self::assertCount( 3, $this->feedbacks->get( 'test' ) );
	}

	public function testFirst() : void
	{
		self::assertNull( Feedbacks::new()->first() );

		self::assertSame( 'unit', $this->feedbacks->first()?->getKey() );
		self::assertSame( 'Unit-Message 1', $this->feedbacks->first()?->getMessage() );
		self::assertSame( 'low', $this->feedbacks->first()?->getSeverity() );
	}

	public function testMap() : void
	{
		$expectedMessages = [
			'Unit-Message 1',
			'Unit-Message 2',
			'Test-Message 3',
			'Test-Message 4',
			'Test-Message 5',
		];

		self::assertSame(
			$expectedMessages,
			$this->feedbacks->map( static fn( FeedbackInterface $fb ) : string => $fb->getMessage() )
		);
	}

	public function testAdd() : void
	{
		$feedbacks = $this->feedbacks->add( Feedback::new( 'foo', 'Bar', 'low' ) );

		self::assertCount( 5, $this->feedbacks );
		self::assertCount( 6, $feedbacks );

		self::assertSame( $this->feedbacks->first(), $feedbacks->first() );
	}

	public function testCount() : void
	{
		self::assertCount( 5, $this->feedbacks );
	}

	public function testFilter() : void
	{
		self::assertCount(
			4,
			$this->feedbacks->filter(
				static fn( FeedbackInterface $fb ) : bool => in_array( $fb->getSeverity(), ['low', 'high'], true )
			)
		);
	}

	public function testHas() : void
	{
		self::assertTrue( $this->feedbacks->has( 'unit' ) );
		self::assertTrue( $this->feedbacks->has( 'test' ) );

		self::assertFalse( $this->feedbacks->has( 'foo' ) );
	}

	public function testGetFirst() : void
	{
		$unit = $this->feedbacks->getFirst( 'unit' );

		self::assertSame( 'unit', $unit?->getKey() );
		self::assertSame( 'Unit-Message 1', $unit?->getMessage() );
		self::assertSame( 'low', $unit?->getSeverity() );

		$test = $this->feedbacks->getFirst( 'test' );

		self::assertSame( 'test', $test?->getKey() );
		self::assertSame( 'Test-Message 3', $test?->getMessage() );
		self::assertSame( 'high', $test?->getSeverity() );

		$notExisting = $this->feedbacks->getFirst( 'not-existing' );

		self::assertNull( $notExisting );
		self::assertNull( $notExisting?->getKey() );
		self::assertNull( $notExisting?->getMessage() );
		self::assertNull( $notExisting?->getSeverity() );
	}

	/**
	 * @throws JsonException
	 */
	public function testJsonSerialize() : void
	{
		$expectedJson = json_encode(
			[
				['key' => 'unit', 'message' => 'Unit-Message 1', 'severity' => 'low'],
				['key' => 'unit', 'message' => 'Unit-Message 2', 'severity' => 'low'],
				['key' => 'test', 'message' => 'Test-Message 3', 'severity' => 'high'],
				['key' => 'test', 'message' => 'Test-Message 4', 'severity' => 'high'],
				['key' => 'test', 'message' => 'Test-Message 5', 'severity' => 'normal'],
			],
			JSON_THROW_ON_ERROR
		);

		self::assertJsonStringEqualsJsonString( $expectedJson, json_encode( $this->feedbacks, JSON_THROW_ON_ERROR ) );
	}
}
