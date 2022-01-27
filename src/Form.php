<?php declare(strict_types=1);

namespace IceHawk\Forms;

use Exception;
use IceHawk\Forms\Exceptions\TokenHasExpiredException;
use IceHawk\Forms\Exceptions\TokenMismatchException;
use IceHawk\Forms\Feedbacks\Feedbacks;
use IceHawk\Forms\Interfaces\FeedbackCollectionInterface;
use IceHawk\Forms\Interfaces\FeedbackInterface;
use IceHawk\Forms\Interfaces\FormIdInterface;
use IceHawk\Forms\Interfaces\FormInterface;
use IceHawk\Forms\Interfaces\TokenInterface;
use IceHawk\Forms\Security\Token;
use JetBrains\PhpStorm\ArrayShape;

final class Form implements FormInterface
{
	private TokenInterface $token;

	/** @var array<string, mixed> */
	private array $data;

	private FeedbackCollectionInterface $feedbacks;

	private bool $dataWasSet;

	/**
	 * @param FormIdInterface $formId
	 *
	 * @return Form
	 * @throws Exception
	 */
	public static function new( FormIdInterface $formId ) : self
	{
		return new self( $formId );
	}

	/**
	 * @param FormIdInterface $formId
	 *
	 * @throws Exception
	 */
	private function __construct( private FormIdInterface $formId )
	{
		$this->reset();
	}

	/**
	 * @throws Exception
	 */
	public function reset() : void
	{
		$this->data       = [];
		$this->dataWasSet = false;

		$this->resetFeedbacks();
		$this->renewToken();
	}

	public function resetFeedbacks() : void
	{
		$this->feedbacks = Feedbacks::new();
	}

	public function getFormId() : FormIdInterface
	{
		return $this->formId;
	}

	/**
	 * @param TokenInterface|null $token
	 *
	 * @throws Exception
	 */
	public function renewToken( ?TokenInterface $token = null ) : void
	{
		$this->token = $token ?? Token::new();
	}

	public function isTokenValid( TokenInterface $token ) : bool
	{
		return ($this->token->equals( $token ) && !$this->token->isExpired());
	}

	/**
	 * @param TokenInterface $token
	 *
	 * @throws TokenHasExpiredException
	 * @throws TokenMismatchException
	 */
	public function guardTokenIsValid( TokenInterface $token ) : void
	{
		if ( !$this->token->equals( $token ) )
		{
			throw TokenMismatchException::withTokens( $this->token, $token );
		}

		if ( $this->token->isExpired() )
		{
			throw TokenHasExpiredException::new();
		}
	}

	public function hasTokenExpired() : bool
	{
		return $this->token->isExpired();
	}

	public function getToken() : TokenInterface
	{
		return $this->token;
	}

	public function setData( array $data ) : void
	{
		$this->data       = $data;
		$this->dataWasSet = true;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getData() : array
	{
		return $this->data;
	}

	public function isset( string $key ) : bool
	{
		return isset( $this->data[ $key ] );
	}

	public function get( string $key ) : mixed
	{
		return $this->data[ $key ] ?? null;
	}

	public function set( string $key, mixed $value ) : void
	{
		$this->data[ $key ] = $value;
		$this->dataWasSet   = true;
	}

	public function unset( string $key ) : void
	{
		unset( $this->data[ $key ] );
	}

	public function wasDataSet() : bool
	{
		return $this->dataWasSet;
	}

	public function addFeedbacks( FeedbackInterface $feedback, FeedbackInterface ...$feedbacks ) : void
	{
		$this->feedbacks = $this->feedbacks->add( $feedback, ...$feedbacks );
	}

	public function hasFeedbacks() : bool
	{
		return $this->feedbacks->count() > 0;
	}

	public function getFeedbacks() : FeedbackCollectionInterface
	{
		return $this->feedbacks;
	}

	/**
	 * @return array<string, mixed>
	 */
	#[ArrayShape(
		[
			'formId'     => FormIdInterface::class,
			'token'      => TokenInterface::class,
			'data'       => 'array',
			'feedbacks'  => FeedbackCollectionInterface::class,
			'dataWasSet' => 'bool',
		]
	)]
	public function jsonSerialize() : array
	{
		return [
			'formId'     => $this->formId,
			'token'      => $this->token,
			'data'       => $this->data,
			'feedbacks'  => $this->feedbacks,
			'dataWasSet' => $this->dataWasSet,
		];
	}
}
