<?php declare(strict_types=1);

namespace IceHawk\Forms\Feedbacks;

use IceHawk\Forms\Interfaces\FeedbackInterface;
use JetBrains\PhpStorm\ArrayShape;

class Feedback implements FeedbackInterface
{
	final public static function new( string $key, string $message, string $severity ) : static
	{
		return new static( $key, $message, $severity );
	}

	final private function __construct( private string $key, private string $message, private string $severity ) { }

	public function getKey() : string
	{
		return $this->key;
	}

	public function getMessage() : string
	{
		return $this->message;
	}

	public function getSeverity() : string
	{
		return $this->severity;
	}

	/**
	 * @return array<string, string>
	 */
	#[ArrayShape(
		[
			'key'      => 'string',
			'message'  => 'string',
			'severity' => 'string',
		]
	)]
	public function jsonSerialize() : array
	{
		return [
			'key'      => $this->key,
			'message'  => $this->message,
			'severity' => $this->severity,
		];
	}
}
