<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms\Interfaces;

/**
 * Interface CreatesToken
 * @package IceHawk\Forms\Interfaces
 */
interface IdentifiesFormRequest extends Identifies
{
	public function equals( IdentifiesFormRequest $other ) : bool;
}