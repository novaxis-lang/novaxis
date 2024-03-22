<?php
namespace Novaxis\Core\Error;

/**
 * Class Exception
 *
 * Custom exception class extending the base \Exception class.
 *
 * @package Novaxis\Core\Error
 */
class Exception extends \Exception {
	/**
	 * The default global error message for the exception.
	 *
	 * @var string
	 */
	protected $global_message = "Unknown error";

	/**
	 * The line number where the exception occurred.
	 *
	 * @var int
	 */
	protected int $line = 0;

	/**
	 * Exception constructor.
	 *
	 * @param string|null $message The custom error message for the exception (optional).
	 * @param int $line The line number where the exception occurred.
	 */
	public function __construct($message = null, int $line){
		$this -> line = $line;
		parent::__construct($message ?? $this -> global_message);
	}

	/**
	 * Set the line number where the exception occurred.
	 *
	 * @param int $line The line number.
	 */
	public function setLineNumber(int $line) {
		$this -> line = $line;
	}

	/**
	 * Get the string representation of the exception.
	 *
	 * @return string The formatted error message with the line number.
	 */
	public function __toString(){
		  return "Error on line {$this -> line}: {$this -> message}";
	}
}