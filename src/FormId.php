<?php declare(strict_types=1);

namespace IceHawk\Forms;

use IceHawk\Forms\Exceptions\InvalidFormIdException;
use IceHawk\Forms\Interfaces\FormIdInterface;

class FormId implements FormIdInterface
{
	public static function new( string $formId ) : self
	{
		return new self( trim( $formId ) );
	}

	final private function __construct( private string $formId )
	{
		$this->guardValueIsValid( $formId );
	}

	/**
	 * @param string $formId
	 *
	 * @throws InvalidFormIdException
	 */
	private function guardValueIsValid( string $formId ) : void
	{
		if ( '' === $formId )
		{
			throw InvalidFormIdException::empty();
		}
	}

	public function toString() : string
	{
		return $this->formId;
	}

	public function __toString() : string
	{
		return $this->toString();
	}

	public function jsonSerialize() : string
	{
		return $this->toString();
	}
}
