<?php
/**
 * @author hollodotme
 */

namespace IceHawk\Forms;

use IceHawk\Forms\Interfaces\ProvidesFeedback;

/**
 * Class Feedback
 * @package IceHawk\Forms
 */
class Feedback implements ProvidesFeedback
{
	const SUCCESS = 'success';

	const NOTICE  = 'notice';

	const WARNING = 'warning';

	const ERROR   = 'error';

	const NONE    = '';

	/** @var string */
	private $message;

	/** @var string */
	private $level;

	/**
	 * @param string $message
	 * @param string $level
	 */
	public function __construct( string $message, string $level = self::ERROR )
	{
		$this->message = $message;
		$this->level   = $level;
	}

	/**
	 * @return string
	 */
	public function getMessage() : string
	{
		return $this->message;
	}

	/**
	 * @return string
	 */
	public function getLevel() : string
	{
		return $this->level;
	}

	public function jsonSerialize()
	{
		return [
			'message' => $this->message,
			'level'   => $this->level,
		];
	}
}