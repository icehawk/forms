<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms\Defaults;

use IceHawk\Forms\Interfaces\IdentifiesFormRequest;

/**
 * Class Token
 * @package IceHawk\Forms\Defaults
 */
final class Token implements IdentifiesFormRequest
{
	/** @var string */
	private $token;

	public function __construct()
	{
		$this->token = random_bytes( 64 );
	}

	/**
	 * @return string
	 */
	public function toString() : string
	{
		return $this->token;
	}

	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->toString();
	}

	/**
	 * @return string
	 */
	public function jsonSerialize()
	{
		return $this->toString();
	}

	/**
	 * @param IdentifiesFormRequest $other
	 *
	 * @return bool
	 */
	public function equals( IdentifiesFormRequest $other ) : bool
	{
		return ($this->toString() == $other->toString());
	}
}