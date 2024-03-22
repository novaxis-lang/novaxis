<?php
namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

/**
 * Class InvalidDataTypeException
 *
 * Exception class for handling invalid or unsupported data type errors.
 *
 * @package Novaxis\Core\Error
 */
class InvalidDataTypeException extends Exception {
	/**
	 * The default error message for the exception.
	 *
	 * @var string
	 */
	protected $message = 'Invalid or unsupported data type. Please enter a valid data type.';
	
	/**
	 * InvalidDataTypeException constructor.
	 *
	 * @param string|null $message The custom error message for the exception (optional).
	 * @param int $line The line number where the exception occurred.
	 */
	function __construct(?string $message = null, int $line = 0){
		parent::__construct($message ?? $this -> message, $line);
	}
}