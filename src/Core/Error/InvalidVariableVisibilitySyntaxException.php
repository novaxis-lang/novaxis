<?php
namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

/**
 * Class InvalidVariableVisibilitySyntaxException
 *
 * Exception class for handling errors related to invalid variable visibility syntax.
 *
 * @package Novaxis\Core\Error
 */
class InvalidVariableVisibilitySyntaxException extends Exception {
	/**
	 * The default error message for the exception.
	 *
	 * @var string
	 */
	protected $message = 'Invalid visibility of the variable syntax.';

	/**
	 * InvalidVariableVisibilitySyntaxException constructor.
	 *
	 * @param int $line The line number where the exception occurred.
	 */
	public function __construct(int $line = 0) {
		parent::__construct($this->message, $line);
	}
}
