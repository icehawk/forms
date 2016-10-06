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

	public function getFormId() : IdentifiesForm
	{
		return $this->formId;
	}

	public function renewToken( IdentifiesFormRequestSource $token = null )
	{
		if ( null === $token )
		{
			$this->token = new Token();
		}
		else
		{
			$this->token = $token;
		}
	}

	public function isTokenValid( IdentifiesFormRequestSource $token ) : bool
	{
		return ($this->token->equals( $token ) && !$this->token->isExpired());
	}

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

	public function getToken() : IdentifiesFormRequestSource
	{
		return $this->token;
	}

	public function setData( array $data )
	{
		$this->data       = $data;
		$this->dataWasSet = true;
	}

	public function getData(): array
	{
		return $this->data;
	}

	public function isset( string $key ) : bool
	{
		return isset($this->data[ $key ]);
	}

	public function get( string $key )
	{
		return $this->data[ $key ] ?? null;
	}

	public function set( string $key, $value )
	{
		$this->data[ $key ] = $value;
		$this->dataWasSet   = true;
	}

	public function unset( string $key )
	{
		unset($this->data[ $key ]);
	}

	public function wasDataSet() : bool
	{
		return $this->dataWasSet;
	}

	public function addFeedbacks( array $feedbacks )
	{
		foreach ( $feedbacks as $key => $feedback )
		{
			$this->addFeedback( $key, $feedback );
		}
	}

	public function addFeedback( string $key, ProvidesFeedback $feedback )
	{
		$this->feedbacks[ $key ] = $feedback;
	}

	public function hasFeedback( string $key ) : bool
	{
		return isset($this->feedbacks[ $key ]);
	}

	public function hasFeedbacks() : bool
	{
		return !empty($this->feedbacks);
	}

	public function getFeedback( string $key ) : ProvidesFeedback
	{
		if ( $this->hasFeedback( $key ) )
		{
			return $this->feedbacks[ $key ];
		}

		return new Feedback( '', Feedback::NONE );
	}

	public function getFeedbacks( callable $filter = null ) : array
	{
		if ( null === $filter )
		{
			return $this->feedbacks;
		}

		return array_filter( $this->feedbacks, $filter, ARRAY_FILTER_USE_BOTH );
	}

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
