<?php
namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

/**
 * Class AutotypeConversionException
 *
 * Exception class representing an autotype conversion failure.
 * This exception is thrown when there is no suitable datatype found to convert the value.
 *
 * @package Novaxis\Core\Error
 */
class AutotypeConversionException extends Exception {
	/**
	 * The default error message for the autotype conversion exception.
	 *
	 * @var string
	 */
	protected $message = 'Autotype conversion failed. No suitable datatype found to convert the value.';

	/**
	 * AutotypeConversionException constructor.
	 *
	 * @param string|null $message The custom error message for the exception (optional).
	 * @param int $line The line number where the exception occurred (optional).
	 */
	function __construct(?string $message = null, int $line = 0){
		parent::__construct($message ?? $this -> message, $line);
	}
}