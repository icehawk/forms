<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms\Tests\Unit;

use IceHawk\Forms\FormId;

class FormIdTest extends \PHPUnit_Framework_TestCase
{
	public function testCanBeRepresentedAsString()
	{
		$formId = new FormId( 'test-form-id' );

		$this->assertEquals( 'test-form-id', (string)$formId );
		$this->assertEquals( 'test-form-id', $formId->toString() );
	}

	public function testCanBeEncodedAsJson()
	{
		$formId = new FormId( 'test-form-id' );

		$this->assertEquals( '"test-form-id"', json_encode( $formId ) );
	}
}
