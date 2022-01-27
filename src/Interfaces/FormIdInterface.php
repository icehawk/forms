<?php declare(strict_types=1);

namespace IceHawk\Forms\Interfaces;

use JsonSerializable;
use Stringable;

interface FormIdInterface extends Stringable, JsonSerializable
{
	public function toString() : string;
}
