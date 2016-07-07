<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms\Interfaces;

/**
 * Interface CreatesToken
 * @package IceHawk\Forms\Interfaces
 */
interface IdentifiesFormRequestSource extends Identifies
{
	public function equals( IdentifiesFormRequestSource $other ) : bool;

	public function isExpired() : bool;
}