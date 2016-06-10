<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms\Interfaces;

/**
 * Interface ProvidesFormData
 * @package IceHawk\Forms\Interfaces
 */
interface ProvidesFormData
{
	public function getData() : array;

	public function getValue( string $key );

	public function hasValue( string $key ) : bool;
}