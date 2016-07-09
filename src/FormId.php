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
	private $formId;

	/**
	 * @param string $formId
	 */
	public function __construct( string $formId )
	{
		$this->formId = $formId;
	}

	/**
	 * @return string
	 */
	public function toString() : string
	{
		return $this->formId;
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