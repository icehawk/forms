<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms\Exceptions;

/**
 * Class InvalidTokenString
 * @package IceHawk\Forms\Exceptions
 */
final class InvalidTokenString extends FormsException
{
	/** @var string */
	private $tokenString;

	public function getTokenString(): string
	{
		return $this->tokenString;
	}

	public function withTokenString( string $tokenString ): self
	{
		$this->tokenString = $tokenString;

		return $this;
	}
}