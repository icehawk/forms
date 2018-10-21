<?php declare(strict_types=1);
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

namespace IceHawk\Forms\Exceptions;

use IceHawk\Forms\Interfaces\IdentifiesFormRequestSource;

/**
 * Class TokenMismatch
 * @package IceHawk\Forms\Exceptions
 */
final class TokenMismatch extends FormsException
{
	/** @var IdentifiesFormRequestSource */
	private $formToken;

	/** @var IdentifiesFormRequestSource */
	private $checkToken;

	public function withTokens(
		IdentifiesFormRequestSource $formToken,
		IdentifiesFormRequestSource $checkToken
	) : TokenMismatch
	{
		$this->formToken = $formToken;
		$this->checkToken = $checkToken;

		return $this;
	}

	public function getFormToken() : IdentifiesFormRequestSource
	{
		return $this->formToken;
	}

	public function getCheckToken() : IdentifiesFormRequestSource
	{
		return $this->checkToken;
	}
}
