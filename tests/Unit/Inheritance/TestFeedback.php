<?php declare(strict_types=1);

namespace IceHawk\Forms\Tests\Unit\Inheritance;

use IceHawk\Forms\Feedbacks\Feedback;

final class TestFeedback extends Feedback
{
	public static function test( string $key, string $message ) : self
	{
		return self::new( $key, $message, 'test' );
	}
}