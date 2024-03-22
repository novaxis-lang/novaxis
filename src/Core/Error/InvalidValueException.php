<?php
namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

/**
 * Class InvalidValueException
 *
 * Exception class for handling errors related to invalid or empty values.
 *
 * @package Novaxis\Core\Error
 */
class InvalidValueException extends Exception {
	/**
	 * The default error message for the exception.
	 *
	 * @var string
	 */
	protected $message = 'The provided value is empty or not valid for the specified data type.';

	/**
	 * InvalidValueException constructor.
	 *
	 * @param string|null $message The custom error message for the exception (optional).
	 * @param int $line The line number where the exception occurred.
	 */
	function __construct(?string $message = null, int $line = 0){
		parent::__construct($message ?? $this -> message, $line);
	}
}