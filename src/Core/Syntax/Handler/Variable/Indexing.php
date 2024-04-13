<?php
namespace Novaxis\Core\Syntax\Handler\Variable;

use Novaxis\Core\Error\InvalidIndexException;
use Novaxis\Core\Error\IndexOutOfRangeException;
use Novaxis\Core\Error\IndexingValueNotExistException;

class Indexing {
	public string $pattern = "/^(\s*\S{1,}\s*)(\[\s*(\S*)\s*\])$/";
	public array $index_declaration_patterns = [
		"/^\d*$/", // [0] -> first element
		"/^\d{1,}\:$/", // [0:] -> from the first element to the end
		"/^\d{1,}\:\d{1,}$/", // [0:1] -> first element before second element
		"/^\:\d{1,}$/", // [:1] -> all elements before the second element
		"/^\:$/", // [:] -> all elements
	];

	public string $index_declaration_public_pattern = "/^(\-?\d*)(\:?)(\-?\d*)$/"; // Public pattern

	public ?string $input = null;

	public function __construct(?string $input = null) {
		$this -> input = $input;
	}

	public function setValue(?string $input = null) {
		$this -> input = $input;

		return $this;
	}

	public function getValue(): ?string {
		return $this -> input;
	}

	public function reset() {
		$this -> setValue();

		return $this;
	}

	
	public function is(?string $input = null) {
		return preg_match($this -> pattern, $input ?? $this -> input);
	}

	public function valueExists(bool $throw = false) {
		if ($this -> input && $this -> is()) {
			return true;
		}
		return $throw == true ? throw new IndexingValueNotExistException : false;
	}
	
	public function getPath() {
		$this -> valueExists(true);
		preg_match($this -> pattern, $this -> input, $matches);
		return trim($matches[1]);
	}

	public function getIndex() {
		$this -> valueExists(true);
		preg_match($this -> pattern, $this -> input, $matches);
		return trim($matches[3]);
	}

	public function getSlice(mixed $value, string $datatype) {
		$this -> valueExists(true);
		$datatype = strtolower($datatype);
		
		if (is_string($value) && $datatype == "string") {
			return $this -> getStringSlice($value);
		}
		else if ((is_array($value) || is_array(json_decode($value))) && $datatype == "list") {
			if (is_array($value)) {
				return $this -> getListSlice($value);
			}
			else {
				return $this -> getListSlice(json_decode($value));
			}
		}
	}

	// public function getIndexes() {
	// 	preg_match($this -> index_declaration_public_pattern, $this -> getIndex(), $matches);

	// 	return array(
	// 		"first-index" => !empty($matches[1]) ? trim($matches[1]) : '0',
	// 		"second-index" => !empty($matches[2]) && !empty($matches[3]) ? trim($matches[3]) : null,
	// 	);
	// }

	public function getStringSlice(string $value) {
		$this -> valueExists(true);
		preg_match($this -> index_declaration_public_pattern, $this -> getIndex(), $matches);
		if (isset($matches[1]) && (!empty($matches[1]) || trim($matches[1]) == '0') && empty($matches[2])) {
			if (isset($value[(int) trim($matches[1])])) {
				return $value[(int) trim($matches[1])];
			}
			else {
				throw new IndexOutOfRangeException('Index \'' . trim($matches[1]) . '\' out of range.');
			}
		}

		else if (!empty($matches[2])) {
			$firstNumber = !empty($matches[1]) ? trim($matches[1]) : '0';
			$secondNumber = !empty($matches[2]) && !empty($matches[3]) ? trim($matches[3]) : null;

			if (!isset($value[$firstNumber])) {
				throw new IndexOutOfRangeException('Index \'' . "$firstNumber in $this->input" . '\' out of range.');
			}
			else if (!isset($value[$secondNumber])) {
				throw new IndexOutOfRangeException('Index \'' . "$secondNumber in $this->input" . '\' out of range.');
			}

			return substr($value, $firstNumber, $secondNumber);
		}
	}

	public function getListSlice(array $value): array {
		$this -> valueExists(true);
		preg_match($this -> index_declaration_public_pattern, $this -> getIndex(), $matches);

		if (isset($matches[1]) && (!empty($matches[1]) || trim($matches[1]) == '0') && empty($matches[2])) {
			if (isset($value[trim($matches[1])])) {
				return [ $value[trim($matches[1])] ];
			}
			else if (isset($value[count($value) + trim($matches[1])])) {
				return [ $value[count($value) + trim($matches[1])] ];
			}
			else {
				throw new IndexOutOfRangeException('Index \'' . trim($matches[1]) . '\' out of range.');
			}
		}

		else if (!empty($matches[2])) {
			$firstNumber = !empty($matches[1]) ? trim($matches[1]) : '0';
			$secondNumber = !empty($matches[2]) && !empty($matches[3]) ? trim($matches[3]) : null;

			if (!isset($value[$firstNumber]) and empty($value[count($value) + $firstNumber])) {
				throw new IndexOutOfRangeException('Index \'' . "$firstNumber in $this->input" . '\' out of range.');
			}
			else if ($secondNumber === null) {}
			else if (!isset($value[$secondNumber]) and (empty($value[count($value) + $secondNumber]))) {
				throw new IndexOutOfRangeException('Index \'' . "$secondNumber in $this->input" . '\' out of range.');
			}
			
			if ($secondNumber === null) {
				$result = array_slice($value, $firstNumber);
			}
			else {
				$result = array_slice($value, $firstNumber, $secondNumber - $firstNumber);
				// $result = array_merge($result, [$value[end($result)]]);
			}

			return $result;
		}
		else {
			throw new InvalidIndexException;
		}
	}
}
