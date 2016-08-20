<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms;

use IceHawk\Forms\Exceptions\TokenHasExpired;
use IceHawk\Forms\Exceptions\TokenMismatch;
use IceHawk\Forms\Interfaces\IdentifiesForm;
use IceHawk\Forms\Interfaces\IdentifiesFormRequestSource;
use IceHawk\Forms\Interfaces\ProvidesFeedback;
use IceHawk\Forms\Interfaces\ProvidesFormData;
use IceHawk\Forms\Security\Token;

/**
 * Class Form
 * @package IceHawk\Forms
 */
class Form implements ProvidesFormData
{
	/** @var IdentifiesForm */
	private $formId;

	/** @var IdentifiesFormRequestSource */
	private $token;

	/** @var array */
	private $data;

	/** @var array|ProvidesFeedback[] */
	private $feedbacks;

	/** @var bool */
	private $dataWasSet;

	/**
	 * @param IdentifiesForm $formId
	 */
	public function __construct( IdentifiesForm $formId )
	{
		$this->formId = $formId;
		$this->reset();
	}

	public function reset()
	{
		$this->data       = [];
		$this->dataWasSet = false;

		$this->resetFeedbacks();
		$this->renewToken();
	}

	public function resetFeedbacks()
	{
		$this->feedbacks = [];
	}

	/**
	 * @return IdentifiesForm
	 */
	public function getFormId() : IdentifiesForm
	{
		return $this->formId;
	}

	/**
	 * @param IdentifiesFormRequestSource|null $token
	 */
	public function renewToken( IdentifiesFormRequestSource $token = null )
	{
		if ( $token === null )
		{
			$this->token = new Token();
		}
		else
		{
			$this->token = $token;
		}
	}

	/**
	 * @param IdentifiesFormRequestSource $token
	 *
	 * @return bool
	 */
	public function isTokenValid( IdentifiesFormRequestSource $token ) : bool
	{
		return ($this->token->equals( $token ) && !$this->token->isExpired());
	}

	/**
	 * @param IdentifiesFormRequestSource $token
	 *
	 * @throws TokenHasExpired
	 * @throws TokenMismatch
	 */
	public function guardTokenIsValid( IdentifiesFormRequestSource $token )
	{
		if ( !$this->token->equals( $token ) )
		{
			throw (new TokenMismatch())->withTokens( $this->token, $token );
		}

		if ( $this->token->isExpired() )
		{
			throw new TokenHasExpired();
		}
	}

	public function hasTokenExpired() : bool
	{
		return $this->token->isExpired();
	}

	/**
	 * @return IdentifiesFormRequestSource
	 */
	public function getToken() : IdentifiesFormRequestSource
	{
		return $this->token;
	}

	/**
	 * @param array $data
	 */
	public function setData( array $data )
	{
		$this->data       = $data;
		$this->dataWasSet = true;
	}

	/**
	 * @return array
	 */
	public function getData(): array
	{
		return $this->data;
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function isset( string $key ) : bool
	{
		return isset($this->data[ $key ]);
	}

	/**
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	public function get( string $key )
	{
		return $this->data[ $key ] ?? null;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	public function set( string $key, $value )
	{
		$this->data[ $key ] = $value;
		$this->dataWasSet   = true;
	}

	/**
	 * @param string $key
	 */
	public function unset( string $key )
	{
		unset($this->data[ $key ]);
	}

	/**
	 * @return bool
	 */
	public function wasDataSet() : bool
	{
		return $this->dataWasSet;
	}

	/**
	 * @param array|ProvidesFeedback[] $feedbacks
	 */
	public function addFeedbacks( array $feedbacks )
	{
		foreach ( $feedbacks as $key => $feedback )
		{
			$this->addFeedback( $key, $feedback );
		}
	}

	/**
	 * @param string           $key
	 * @param ProvidesFeedback $feedback
	 */
	public function addFeedback( string $key, ProvidesFeedback $feedback )
	{
		$this->feedbacks[ $key ] = $feedback;
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function hasFeedback( string $key ) : bool
	{
		return isset($this->feedbacks[ $key ]);
	}

	/**
	 * @return bool
	 */
	public function hasFeedbacks() : bool
	{
		return !empty($this->feedbacks);
	}

	/**
	 * @param string $key
	 *
	 * @return ProvidesFeedback
	 */
	public function getFeedback( string $key ) : ProvidesFeedback
	{
		if ( $this->hasFeedback( $key ) )
		{
			return $this->feedbacks[ $key ];
		}

		return new Feedback( '', Feedback::NONE );
	}

	/**
	 * @param callable $filter
	 *
	 * @return array|Interfaces\ProvidesFeedback[]
	 */
	public function getFeedbacks( callable $filter = null ) : array
	{
		if ( $filter === null )
		{
			return $this->feedbacks;
		}

		return array_filter( $this->feedbacks, $filter, ARRAY_FILTER_USE_BOTH );
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
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