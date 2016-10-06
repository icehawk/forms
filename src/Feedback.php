<?php declare(strict_types = 1);
/**
 * Copyright (c) 2016 Holger Woltersdorf & Contributors
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
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

	public function __construct( string $message, string $severity = self::ERROR )
	{
		$this->message  = $message;
		$this->severity = $severity;
	}

	public function getMessage() : string
	{
		return $this->message;
	}

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
