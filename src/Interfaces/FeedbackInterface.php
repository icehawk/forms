<?php declare(strict_types=1);

namespace IceHawk\Forms\Interfaces;

use JsonSerializable;

interface FeedbackInterface extends JsonSerializable
{
	public function getKey() : string;

	public function getMessage() : string;

	public function getSeverity() : string;
}
