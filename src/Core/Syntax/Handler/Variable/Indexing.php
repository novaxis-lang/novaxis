<?php
namespace Novaxis\Core\Syntax\Handler\Variable;

use Novaxis\Core\Error\InvalidIndexException;
use Novaxis\Core\Error\IndexOutOfRangeException;
use Novaxis\Core\Error\IndexingValueNotExistException;
use Novaxis\Core\Error\InvalidIndexingTargetException;
use Novaxis\Core\Error\MultidimensionalIndexingWithStringException;

class Indexing {
	public string $pattern = '/^(\s*(\w+|\.){1,}\s*)(\[\s*\S*\s*\]){1,}$/';
	public array $index_declaration_patterns = [
		"/^\d*$/", // [0] -> first element
		"/^\d{1,}\:$/", // [0:] -> from the first element to the end
		"/^\d{1,}\:\d{1,}$/", // [0:1] -> first element before second element
		"/^\:\d{1,}$/", // [:1] -> all elements before the second element
		"/^\:$/", // [:] -> all elements
	];

	public string $index_declaration_public_pattern = '/^(\-?\d*)(\:?)(\-?\d*)$/'; // Public pattern
	public string $index_pattern = '/\[\s*(\-?\d*?\:?\-?\d*?)\s*\]/';

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

	public function getIndex(string $index) { // or `extractIndex` as a name for the function
		$this -> valueExists(true);
		preg_match($this -> index_pattern, $index, $matches);
		return $matches[1];
	}

	public function getIndexes(): array {
		$this -> valueExists(true);
		preg_match($this -> pattern, $this -> input, $matches);
		$indexes = trim($matches[3]);
		preg_match_all($this -> index_pattern, $indexes, $matches);
		return $matches[0];
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

	public function getStringSlice(string $value): string {
		$this -> valueExists(true);

		foreach ($this -> getIndexes() as $_ => $index) {
			if (strlen($value) <= 1) {
				throw new InvalidIndexingTargetException;
			}
			preg_match($this -> index_declaration_public_pattern, $this -> getIndex($index), $matches);
			if (isset($matches[1]) && (!empty($matches[1]) || trim($matches[1]) == '0') && empty($matches[2])) {
				if (isset($value[(int) trim($matches[1])])) {
					$value = $value[(int) trim($matches[1])];
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
	
				$value = substr($value, $firstNumber, $secondNumber);
			}
		}

		return $value;
	}

	public function getListSlice(array $value): mixed {
		$this -> valueExists(true);
		foreach ($this -> getIndexes() as $_ => $index) {
			if (!is_array($value)) {
				throw new InvalidIndexingTargetException;
			}
			preg_match($this -> index_declaration_public_pattern, $this -> getIndex($index), $matches);
	
			if (isset($matches[1]) && (!empty($matches[1]) || trim($matches[1]) == '0') && empty($matches[2])) {
				if (isset($value[trim($matches[1])])) {
					$value = $value[trim($matches[1])];
				}
				else if (isset($value[count($value) + trim($matches[1])])) {
					$value = $value[count($value) + trim($matches[1])];
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
					$value = array_slice($value, $firstNumber);
				}
				else {
					$value = array_slice($value, $firstNumber, $secondNumber - $firstNumber);
				}
			}
			else {
				throw new InvalidIndexException;
			}
		}

		return $value;
	}
}