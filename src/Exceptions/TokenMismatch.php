<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms\Exceptions;

use IceHawk\Forms\Interfaces\IdentifiesFormRequest;

/**
 * Class TokenMismatch
 * @package IceHawk\Forms\Exceptions
 */
final class TokenMismatch extends FormsException
{
	/** @var IdentifiesFormRequest */
	private $formToken;

	/** @var IdentifiesFormRequest */
	private $checkToken;

	/**
	 * @param IdentifiesFormRequest $formToken
	 * @param IdentifiesFormRequest $checkToken
	 *
	 * @return $this
	 */
	public function withTokens( IdentifiesFormRequest $formToken, IdentifiesFormRequest $checkToken )
	{
		$this->formToken  = $formToken;
		$this->checkToken = $checkToken;

		return $this;
	}

	/**
	 * @return IdentifiesFormRequest
	 */
	public function getFormToken()
	{
		return $this->formToken;
	}

	/**
	 * @return IdentifiesFormRequest
	 */
	public function getCheckToken()
	{
		return $this->checkToken;
	}
}