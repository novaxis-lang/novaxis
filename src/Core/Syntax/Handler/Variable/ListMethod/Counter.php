<?php
namespace Novaxis\Core\Syntax\Handler\Variable\ListMethod;

/**
 * Class Counter
 *
 * This class is responsible for handling multi-line lists.
 *
 * @package Novaxis\Core\Syntax\Handler\Variable\ListMethod
 */
class Counter {
	/**
	 * @var string The stored value.
	 */
	private string $value = '';
	
	/**
	 * @var int|null The count of remaining open brackets (nullable).
	 */
	private ?int $remainingOpenBracket = null;
	
	/**
	 * @var array An array containing regular expressions to find brackets.
	 */
	private array $bracketFindPattern = ["/((?<!\\\\)\[)/", "/((?<!\\\\)\])/"];
	
	/**
     * Check if the given variable details indicate a list datatype.
     *
     * @param array $allVariableDetails An array containing variable details.
     * @return bool True if the datatype is 'list', false otherwise.
     */
	public function is(array $allVariableDetails) {
		if (strtolower($allVariableDetails['datatype']) == 'list') {
			return true;
		}

		return false;
	}

	/**
	 * Add a specified number to the remaining open brackets count.
	 *
	 * @param int $number The number of open brackets to add (default is 1).
	 */
	public function add(int $number = 1) {
		$this -> remainingOpenBracket += $number;
		return $this;
	}

	/**
	 * Subtract a specified number from the remaining open brackets count.
	 *
	 * @param int $number The number of open brackets to subtract (default is 1).
	 */
	public function sub(int $number = 1) {
		$this -> remainingOpenBracket -= $number;

		// Ensure the count does not go below zero
		if ($this -> remainingOpenBracket < 0) {
			$this -> remainingOpenBracket = 0;
		}

		return $this; 
	}

	/**
	 * Get the current count of remaining open brackets.
	 *
	 * @return int The count of remaining open brackets.
	 */
	public function get() {
		return $this -> remainingOpenBracket;
	}

	/**
	 * Process a line of code to update the count of remaining open brackets.
	 *
	 * @param string $line The line of code to process.
	 * 
	 * @return $this
	 */
	public function should(string $line) {
		if($this -> remainingOpenBracket === null) {
			$this -> remainingOpenBracket = 0;
		}

		preg_match_all($this -> bracketFindPattern[0], $line, $matches);
		if ($matches[0]) {
			$this -> add(count($matches[0]));
		}

		unset($matches);
		
		preg_match_all($this -> bracketFindPattern[1], $line, $matches);
		if ($matches[0]) {
			$this -> sub(count($matches[0]));
		}

		return $this;
	}

	/**
	 * Check if all open brackets have been closed.
	 * 
	 * @return bool True if all open brackets are closed, false otherwise.
	 */
	public function able() {
		if ($this -> remainingOpenBracket === 0) {
			return true;
		}

		return false;
	}

	/**
	 * Append a string to the storage value.
	 *
	 * @param string $value The string to append to the storage.
	 * @return $this
	 */
	public function storage(string $value) {
		$this -> value .= trim($value);
		return $this;
	}

	/**
	 * Get the current stored value.
	 *
	 * @return string The stored value.
	 */
	public function getStorage() {
		return trim($this -> value);
	}
}