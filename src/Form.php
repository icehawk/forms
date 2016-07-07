<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms;

use IceHawk\Forms\Defaults\Token;
use IceHawk\Forms\Exceptions\TokenMismatch;
use IceHawk\Forms\Interfaces\IdentifiesForm;
use IceHawk\Forms\Interfaces\IdentifiesFormRequestSource;
use IceHawk\Forms\Interfaces\ProvidesFeedback;

/**
 * Class Form
 * @package IceHawk\Forms
 */
class Form
{
	/** @var IdentifiesForm */
	private $id;

	/** @var IdentifiesFormRequestSource */
	private $token;

	/** @var array */
	private $data;

	/** @var array|ProvidesFeedback[] */
	private $feedbacks;

	/**
	 * @param IdentifiesForm $id
	 */
	public function __construct( IdentifiesForm $id )
	{
		$this->id = $id;
		$this->reset();
	}

	public function reset()
	{
		$this->data      = [ ];
		$this->feedbacks = [ ];
		$this->renewToken();
	}

	/**
	 * @return IdentifiesForm
	 */
	public function getId() : IdentifiesForm
	{
		return $this->id;
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
	public function isValidToken( IdentifiesFormRequestSource $token ) : bool
	{
		return $this->token->equals( $token );
	}

	public function validateToken( IdentifiesFormRequestSource $token )
	{
		if ( !$this->isValidToken( $token ) )
		{
			throw ( new TokenMismatch() )->withTokens( $this->token, $token );
		}
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
		$this->data = $data;
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function hasValue( string $key ) : bool
	{
		return isset($this->data[ $key ]);
	}

	/**
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	public function getValue( string $key )
	{
		return $this->data[ $key ] ?? null;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	public function setValue( string $key, $value )
	{
		$this->data[ $key ] = $value;
	}

	/**
	 * @param string $key
	 */
	public function unsetValue( string $key )
	{
		unset($this->data[ $key ]);
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
	 * @param int      $filterFlag (see array_filter)
	 *
	 * @link http://php.net/manual/en/function.array-filter.php
	 * @return array|Interfaces\ProvidesFeedback[]
	 */
	public function getFeedbacks( callable $filter = null, int $filterFlag = 0 ) : array
	{
		if ( $filter === null )
		{
			return $this->feedbacks;
		}

		return array_filter( $this->feedbacks, $filter, $filterFlag );
	}
}