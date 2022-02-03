<?php declare(strict_types=1);

namespace IceHawk\Forms\Interfaces;

use Closure;
use Countable;
use IteratorAggregate;
use JsonSerializable;

/**
 * @extends IteratorAggregate<int, FeedbackInterface>
 */
interface FeedbackCollectionInterface extends IteratorAggregate, Countable, JsonSerializable
{
	public function add( FeedbackInterface $feedback, FeedbackInterface ...$feedbacks ) : FeedbackCollectionInterface;

	public function has( string $key ) : bool;

	public function get( string $key ) : FeedbackCollectionInterface;

	public function getFirst( string $key ) : ?FeedbackInterface;

	public function first() : ?FeedbackInterface;

	/**
	 * @param Closure $mapFunction
	 *
	 * @return array<int, mixed>
	 */
	public function map( Closure $mapFunction ) : array;

	public function filter( Closure $filterFunction ) : FeedbackCollectionInterface;
}