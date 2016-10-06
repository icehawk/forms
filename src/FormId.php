<?php declare(strict_types = 1);
/**
 * Copyright (c) 2016 Holger Woltersdorf & Contributors
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
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

	public function __construct( string $formId )
	{
		$this->formId = $formId;
	}

	public function toString() : string
	{
		return $this->formId;
	}

	public function __toString() : string
	{
		return $this->toString();
	}

	public function jsonSerialize()
	{
		return $this->toString();
	}
}
