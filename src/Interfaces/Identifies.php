<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms\Interfaces;

/**
 * Interface Identifies
 * @package IceHawk\Forms\Interfaces
 */
interface Identifies extends \JsonSerializable
{
	public function toString() : string;

	public function __toString() : string;
}