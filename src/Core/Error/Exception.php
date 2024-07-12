<?php
namespace Novaxis\Core\Error;

/**
 * Class Exception
 *
 * @package Novaxis\Core\Error
 */
class Exception {
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
		$this -> message = $message ?? $this -> global_message;
		$this -> exit();

		# parent::__construct($message ?? $this -> global_message);
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
	 * Outputs the object as a string and terminates the script.
	 *
	 * @return void
	 */
	public function exit() {
		echo $this -> __toString();
		die();
	}

	/**
	 * Get the string representation of the exception.
	 *
	 * @return string The formatted error message with the line number.
	 */
	public function __toString(): string {
		  return "\033[1;31mError\033[0m on line {$this -> line}: {$this -> message}";
	}
}