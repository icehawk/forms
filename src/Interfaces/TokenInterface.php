<?php declare(strict_types=1);

namespace IceHawk\Forms\Interfaces;

use JsonSerializable;
use Stringable;

interface TokenInterface extends Stringable, JsonSerializable
{
	public function equals( TokenInterface $other ) : bool;

	public function isExpired() : bool;

	public function toString() : string;
}
