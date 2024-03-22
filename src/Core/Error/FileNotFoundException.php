<?php
namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

/**
 * Class FileNotFoundException
 *
 * Exception class for handling file not found errors.
 *
 * @package Novaxis\Core\Error
 */
class FileNotFoundException extends Exception {
	/**
	 * The default error message for the exception.
	 *
	 * @var string
	 */
	protected $message = 'The specified file could not be located or accessed.';

	/**
	 * FileNotFoundException constructor.
	 *
	 * @param string|null $message The custom error message for the exception (optional).
	 * @param int $line The line number where the exception occurred.
	 */
	function __construct(?string $message = null, int $line = 0){
		parent::__construct($message ?? $this -> message, $line);
	}
}