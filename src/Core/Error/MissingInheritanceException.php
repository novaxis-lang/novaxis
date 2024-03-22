<?php
namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

/**
 * Class MissingInheritanceException
 *
 * Exception class for handling errors related to missing or invalid datatype inheritance.
 *
 * @package Novaxis\Core\Error
 */
class MissingInheritanceException extends Exception {
	/**
	 * The default error message for the exception.
	 *
	 * @var string
	 */
	protected $message = 'The datatype does not have a valid inheritance. Please make sure to specify a valid parent datatype for the datatype.';

	/**
	 * MissingInheritanceException constructor.
	 *
	 * @param string|null $message The custom error message for the exception (optional).
	 * @param int $line The line number where the exception occurred.
	 */
	function __construct(?string $message = null, int $line = 0){
		parent::__construct($message ?? $this -> message, $line);
	}
}