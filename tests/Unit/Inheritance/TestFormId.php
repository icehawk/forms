<?php declare(strict_types=1);

namespace IceHawk\Forms\Tests\Unit\Inheritance;

use IceHawk\Forms\FormId;

final class TestFormId extends FormId
{
	public static function testForm() : self
	{
		return self::new( 'testForm' );
	}
}