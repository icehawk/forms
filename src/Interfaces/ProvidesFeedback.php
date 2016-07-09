<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms\Interfaces;

/**
 * Interface ProvidesFeedback
 * @package IceHawk\Forms\Interfaces
 */
interface ProvidesFeedback extends \JsonSerializable
{
	public function getMessage() : string;

	public function getSeverity() : string;
}