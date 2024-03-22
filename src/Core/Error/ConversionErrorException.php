<?php
namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

/**
 * Class ConversionErrorException
 *
 * Exception class representing a conversion error.
 * This exception is thrown when an error occurs while converting the value to the desired datatype.
 *
 * @package Novaxis\Core\Error
 */
class ConversionErrorException extends Exception {
	/**
	 * The default error message for the conversion error exception.
	 *
	 * @var string
	 */
	protected $message = 'An error occurred while converting the value to the desired datatype. Please check that the provided value is compatible with the specified datatype.';

	/**
	 * ConversionErrorException constructor.
	 *
	 * @param string|null $message The custom error message for the exception (optional).
	 * @param int $line The line number where the exception occurred (optional).
	 */
	function __construct(?string $message = null, int $line = 0){
		parent::__construct($message ?? $this -> message, $line);
	}
}