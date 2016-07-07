<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms\Exceptions;

/**
 * Class InvalidExpiryInterval
 * @package IceHawk\Forms\Exceptions
 */
final class InvalidExpiryInterval extends FormsException
{
	/** @var int */
	private $seconds;

	public function getSeconds(): int
	{
		return $this->seconds;
	}

	public function withSeconds( int $seconds ): InvalidExpiryInterval
	{
		$this->seconds = $seconds;

		return $this;
	}
}