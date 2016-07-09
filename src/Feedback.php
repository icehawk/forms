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
	private $severity;

	/**
	 * @param string $message
	 * @param string $severity
	 */
	public function __construct( string $message, string $severity = self::ERROR )
	{
		$this->message  = $message;
		$this->severity = $severity;
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
	public function getSeverity() : string
	{
		return $this->severity;
	}

	public function jsonSerialize()
	{
		return [
			'message'  => $this->message,
			'severity' => $this->severity,
		];
	}
}