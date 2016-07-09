<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms\Interfaces;

/**
 * Interface ProvidesFormData
 * @package IceHawk\Forms\Interfaces
 */
interface ProvidesFormData extends \JsonSerializable
{
	public function getData() : array;

	public function get( string $key );

	public function isset( string $key ) : bool;

	public function set( string $key, $value );

	public function unset( string $key );

	public function wasDataSet() : bool;
}