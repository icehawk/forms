<?php declare(strict_types=1);

namespace IceHawk\Forms\Feedbacks;

use Closure;
use IceHawk\Forms\Interfaces\FeedbackCollectionInterface;
use IceHawk\Forms\Interfaces\FeedbackInterface;
use Iterator;
use function array_values;
use function count;

final class Feedbacks implements FeedbackCollectionInterface
{
	public static function new( FeedbackInterface ...$feedbacks ) : self
	{
		return new self( array_values( $feedbacks ) );
	}

	/**
	 * @param array<int, FeedbackInterface> $feedbacks
	 */
	private function __construct( private array $feedbacks ) { }

	public function add( FeedbackInterface $feedback, FeedbackInterface ...$feedbacks ) : self
	{
		return new self( [...$this->feedbacks, $feedback, ...array_values( $feedbacks )] );
	}

	public function has( string $key ) : bool
	{
		return $this->filter( static fn( FeedbackInterface $fb ) : bool => $fb->getKey() === $key )->count() > 0;
	}

	public function get( string $key ) : FeedbackCollectionInterface
	{
		return $this->filter( static fn( FeedbackInterface $fb ) : bool => $fb->getKey() === $key );
	}

	public function getFirst( string $key ) : ?FeedbackInterface
	{
		return $this->get( $key )->first();
	}

	public function first() : ?FeedbackInterface
	{
		return $this->feedbacks[0] ?? null;
	}

	/**
	 * @return Iterator<int, FeedbackInterface>
	 */
	public function getIterator() : Iterator
	{
		yield from $this->feedbacks;
	}

	public function count() : int
	{
		return count( $this->feedbacks );
	}

	/**
	 * @param Closure $mapFunction
	 *
	 * @return array<int, mixed>
	 */
	public function map( Closure $mapFunction ) : array
	{
		return array_map( $mapFunction, $this->feedbacks );
	}

	public function filter( Closure $filterFunction ) : self
	{
		return new self( array_values( array_filter( $this->feedbacks, $filterFunction ) ) );
	}

	/**
	 * @return array<int, FeedbackInterface>
	 */
	public function jsonSerialize() : array
	{
		return $this->feedbacks;
	}
}