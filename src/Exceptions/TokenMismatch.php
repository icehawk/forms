<?php
/**
 * @author hollodotme
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

	/**
	 * @param IdentifiesFormRequestSource $formToken
	 * @param IdentifiesFormRequestSource $checkToken
	 *
	 * @return $this
	 */
	public function withTokens( IdentifiesFormRequestSource $formToken, IdentifiesFormRequestSource $checkToken )
	{
		$this->formToken  = $formToken;
		$this->checkToken = $checkToken;

		return $this;
	}

	/**
	 * @return IdentifiesFormRequestSource
	 */
	public function getFormToken()
	{
		return $this->formToken;
	}

	/**
	 * @return IdentifiesFormRequestSource
	 */
	public function getCheckToken()
	{
		return $this->checkToken;
	}
}