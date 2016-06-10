<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms;

use IceHawk\Forms\Interfaces\IdentifiesForm;

/**
 * Class FormId
 * @package IceHawk\Forms
 */
class FormId implements IdentifiesForm
{
	/** @var string */
	private $id;

	/**
	 * @param string $id
	 */
	public function __construct( string $id )
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function toString() : string
	{
		return $this->id;
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
}