<?php declare(strict_types=1);

namespace IceHawk\Forms\Tests\Unit;

use IceHawk\Forms\Exceptions\InvalidFormIdException;
use IceHawk\Forms\FormId;
use IceHawk\Forms\Tests\Unit\Inheritance\TestFormId;
use JsonException;
use PHPUnit\Framework\TestCase;
use function json_encode;
use const JSON_THROW_ON_ERROR;

final class FormIdTest extends TestCase
{
	public function testToString() : void
	{
		$formId = FormId::new( 'test-form-id' );

		self::assertEquals( 'test-form-id', (string)$formId );
		self::assertEquals( 'test-form-id', $formId->toString() );
	}

	public function testThrowsExceptionForEmptyFormId() : void
	{
		$this->expectException( InvalidFormIdException::class );
		$this->expectExceptionMessage( 'Invalid value for form ID: empty' );

		FormId::new( ' ' );
	}

	/**
	 * @throws JsonException
	 */
	public function testJsonSerialize() : void
	{
		$formId = FormId::new( 'test-form-id' );

		self::assertEquals( '"test-form-id"', json_encode( $formId, JSON_THROW_ON_ERROR ) );
	}

	public function testInheritance() : void
	{
		$formId = TestFormId::testForm();

		self::assertSame( 'testForm', (string)$formId );
	}

	public function testEquals() : void
	{
		$formIdUnit  = FormId::new( 'unit' );
		$formIdUnit2 = FormId::new( 'unit' );
		$formIdTest  = FormId::new( 'test' );

		$customFormIdUnit = TestFormId::new( 'unit' );
		$customFormIdTest = TestFormId::new( 'test' );

		self::assertTrue( $formIdUnit->equals( $formIdUnit2 ) );
		self::assertTrue( $formIdUnit2->equals( $formIdUnit ) );

		self::assertFalse( $formIdUnit->equals( $formIdTest ) );
		self::assertFalse( $formIdTest->equals( $formIdUnit ) );

		self::assertFalse( $formIdUnit2->equals( $formIdTest ) );
		self::assertFalse( $formIdTest->equals( $formIdUnit2 ) );

		self::assertFalse( $formIdUnit->equals( $customFormIdUnit ) );
		self::assertFalse( $formIdTest->equals( $customFormIdTest ) );
	}
}
