<?php declare(strict_types=1);

namespace IceHawk\Forms\Exceptions;

use IceHawk\Forms\Interfaces\TokenInterface;
use RuntimeException;

final class TokenMismatchException extends RuntimeException
{
	private ?TokenInterface $formToken = null;

	private ?TokenInterface $checkToken = null;

	public static function withTokens( TokenInterface $formToken, TokenInterface $checkToken ) : self
	{
		$instance = new self(
			sprintf(
				'Tokens do not match: %s (expected) vs. %s (actual)',
				$formToken,
				$checkToken
			)
		);

		$instance->formToken  = $formToken;
		$instance->checkToken = $checkToken;

		return $instance;
	}

	private function __construct( string $message )
	{
		parent::__construct( $message );
	}

	public function getFormToken() : ?TokenInterface
	{
		return $this->formToken;
	}

	public function getCheckToken() : ?TokenInterface
	{
		return $this->checkToken;
	}
}
