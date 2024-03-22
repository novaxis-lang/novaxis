<?php
namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

/**
 * Class InterpolationPathNotFoundException
 *
 * Exception class for handling interpolation path not found errors.
 *
 * @package Novaxis\Core\Error
 */
class InterpolationPathNotFoundException extends Exception {
	/**
	 * The default error message for the exception.
	 *
	 * @var string
	 */
	protected $message = 'The specified path does not exist in the data structure.';

	/**
	 * InterpolationPathNotFoundException constructor.
	 *
	 * @param string|null $message The custom error message for the exception (optional).
	 * @param int $line The line number where the exception occurred.
	 */
	function __construct(?string $message = null, int $line = 0){
		parent::__construct($message ?? $this -> message, $line);
	}
}