<?php declare(strict_types=1);

namespace IceHawk\Forms\Interfaces;

use IceHawk\Forms\Exceptions\TokenHasExpiredException;
use IceHawk\Forms\Exceptions\TokenMismatchException;
use JsonSerializable;

interface FormInterface extends JsonSerializable
{
	public function getFormId() : FormIdInterface;

	public function reset() : void;

	public function resetFeedbacks() : void;

	public function addFeedbacks( FeedbackInterface $feedback, FeedbackInterface ...$feedbacks ) : void;

	public function hasFeedbacks() : bool;

	public function getFeedbacks() : FeedbackCollectionInterface;

	/**
	 * @param array<string, mixed> $data
	 */
	public function setData( array $data ) : void;

	/**
	 * @return array<string, mixed>
	 */
	public function getData() : array;

	public function get( string $key ) : mixed;

	public function isset( string $key ) : bool;

	public function set( string $key, mixed $value ) : void;

	public function unset( string $key ) : void;

	public function wasDataSet() : bool;

	public function hasTokenExpired() : bool;

	public function getToken() : TokenInterface;

	public function renewToken( ?TokenInterface $token = null ) : void;

	public function isTokenValid( TokenInterface $token ) : bool;

	/**
	 * @param TokenInterface $token
	 *
	 * @throws TokenHasExpiredException
	 * @throws TokenMismatchException
	 */
	public function guardTokenIsValid( TokenInterface $token ) : void;
}
