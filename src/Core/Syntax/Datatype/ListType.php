<?php
namespace Novaxis\Core\Syntax\Datatype;

use Novaxis\Core\Syntax\Datatype\AutoType;
use Novaxis\Core\Error\ConversionErrorException;
use Novaxis\Core\Syntax\Datatype\TypesInterface;

/**
 * Class ListType
 *
 * Represents a list datatype in the Novaxis syntax.
 *
 * @package Novaxis\Core\Syntax\Datatype
 */
class ListType implements TypesInterface {
	/**
	 * @var string The name of the datatype (List).
	 */
	public $dataTypeName = 'List';

	/**
	 * @var array The list items.
	 */
	private array $items = [];

	/**
	 * @var string The input string to be converted to a list.
	 */
	private string $input;

	/**
	 * The AutoType instance for handling auto data type definitions.
	 *
	 * @var AutoType
	 */
	private AutoType $AutoType;

	/**
	 * ListType constructor.
	 */
	public function __construct() {
		$this -> AutoType = new AutoType;
	}
	
	/**
	 * Set the input string value.
	 *
	 * @param string $input The input string to be converted to a list.
	 * @return ListType Returns the current instance for method chaining.
	 */
	public function setValue($input) {
		$this -> input = $input;
		return $this;
	}

	/**
	 * Get the list items.
	 *
	 * @return array The list items.
	 */
	public function getValue() {
		return $this -> items;
	}

	/**
	 * Check if the input is a valid list representation.
	 *
	 * @return bool Returns true if the input is a valid list representation, false otherwise.
	 */
	public function is() {
		// Trim any leading or trailing whitespaces
		$input = trim($this -> input);
	
		// Check if the input starts with '[' and ends with ']'
		return (substr($input, 0, 1) === '[' && substr($input, -1) === ']');
	}

	/**
	 * Convert the input string to a list of items.
	 *
	 * @return $this
	 * @throws ConversionErrorException If the input is not a valid list representation.
	 */
	function convertTo() {
		if (!$this -> is()) {
			throw new ConversionErrorException;
		}

		$result = [];
		$currentItem = '';
		$stack = [];
		$backslash = false;
	
		for ($i = 0; $i < strlen($this -> input); $i++) {
			$letter = $this -> input[$i];
			
			if ($letter == '\\') {
				$backslash = true;
			}

			else if ($letter === '[' && $backslash === false) {
				if (trim($currentItem) !== '') {
					$result[] = trim ($currentItem);
				}

				$currentItem = '';
				array_push($stack, $result);
				$result = [];
			}

			else if ($letter === ']' && $backslash === false) {
				if (trim($currentItem) !== '') {
					$result[] = trim ($currentItem);
				}

				$currentItem = array_pop($stack);
				$currentItem[] = $result;
				$result = $currentItem;
				$currentItem = '';
			}

			else if ($letter === ',' && !$backslash) {
				if (trim($currentItem) !== '') {
					$result[] = trim ($currentItem);
				}

				$currentItem = '';
			}

			else {
				$currentItem .= $letter;
				$backslash = false;
			}
		}
	
		if ($currentItem !== '') {
			$result[] = trim($currentItem);
		}
	
		$this -> items = $this -> filter($result);
		
		return $this;
	}

	/**
	 * Recursively filter and convert an array of values to auto data types.
	 *
	 * This method iterates through an array, recursively filtering and converting values to their corresponding auto data types.
	 *
	 * @param array $list The list of values to filter and convert.
	 * @return array The resulting array with auto data type values.
	 */
	public function filter(array $list) {
		$result = [];

		foreach ($list as $element) {
			if (is_array($element)) {
				$result[] = $this -> filter($element);
			}
			else {
				$this -> AutoType -> setValue($element);
				$this -> AutoType -> convertTo();
				$result[] = $this -> AutoType -> getItem()['value'];
			}
		}

		return $result;
	}
	
	/**
	 * Get the list items after conversion.
	 *
	 * @return array The list items.
	 */
	public function getItems() {
		return $this -> items;
	}

	/**
	 * Remove the first and last character of a string.
	 *
	 * @param string $inputString The input string.
	 * @return string The modified string after removing the first and last characters.
	 */
	public function removeFirstAndLastLetter($inputString) {
		if (strlen($inputString) <= 2) {
			return '';
		} else {
			return substr($inputString, 1, -1);
		}
	}

	/**
	 * Converts an array to its string representation, optionally enclosing nested arrays with brackets.
	 *
	 * @param array $array The array to be converted.
	 * @param bool $final Determines whether to enclose the final result in brackets.
	 * @return string The string representation of the array.
	 */
	public function arrayToString($array, bool $final = true) {
		$result = [];
	
		foreach ($array as $item) {
			if (is_array($item)) {
				$result[] = '[' . $this -> arrayToString($item, false) . ']';
			} else {
				$result[] = strval($item);
			}
		}
	
		if ($final === true) {
			return '[' . implode(', ', $result) . ']';
		}

		return implode(', ', $result);
	}
}