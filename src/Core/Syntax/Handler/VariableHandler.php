<?php
namespace Novaxis\Core\Syntax\Handler;

use Novaxis\Core\Syntax\Handler\Namingrules;
use Novaxis\Core\Error\InvalidValueException;
use Novaxis\Core\Syntax\Token\VariableTokens;

/**
 * The VariableHandler class handles operations related to variables and their tokens.
 */
class VariableHandler {
	use VariableTokens;

	/**
	 * The pattern used for variable processing.
	 *
	 * @var string
	 */
	public string $pattern;

	/**
	 * Instance of the Namingrules class for validating naming rules.
	 *
	 * @var Namingrules
	 */
	private Namingrules $Namingrules;

	/**
	 * Constructs a new VariableHandler class.
	 */
	public function __construct() {
		$this -> Namingrules = new Namingrules;
		
		$values = array_map(function($value) {
			return preg_quote($value);
		}, self::VISIBILITY_KEYWORDS);
		
		$this -> pattern = '/^\s*(' . join('|', array_values($values)) . ')?\s*([A-z0-9_]*)\s*((\?)\s*(.*?)\s*(\((\w|\W){0,}\))?)?\s*(=|:)\s*.+\s*$/i';
	}

	/**
	 * Check if line has valid variable declaration.
	 *
	 * @param string $line The line to check.
	 * @return bool True if it's a valid variable declaration, else false.
	 */
	public function isVariable($line) {
		return preg_match($this -> pattern, $line) && $this -> isValidVariableVisibilitySyntax($line, null) && $this -> getVariableName($line, true);
	}
	
	/**
	 * Check if the visibility of a variable is valid.
	 *
	 * Validates the visibility of a variable based on predefined keywords.
	 *
	 * @param string|null $line The line to analyze for visibility.
	 * @param mixed|null $value The value to check for visibility.
	 * @return bool True if the variable's visibility is valid, false otherwise.
	 */
	public function isValidVariableVisibilitySyntax($line = null, $value = null): bool {
		if (preg_match($this -> pattern, $line, $matches)) {
			if ($matches[1] && !in_array(ucfirst(strtolower($matches[1])), array_values(self::VISIBILITY_KEYWORDS))) {
				return false;
			}
		}
		else if ($value && !in_array(ucfirst(strtolower($value)), array_values(self::VISIBILITY_KEYWORDS))) {
			return false;
		}
		
		return true;
	}

	/**
	 * Get the visibility syntax of a variable.
	 *
	 * Retrieves the visibility syntax of a variable based on the provided line.
	 *
	 * @param string $line The line to extract the visibility from.
	 * @return string The variable's visibility syntax.
	 */
	public function getVariableVisibilitySyntax($line) {
		if (preg_match($this -> pattern, $line, $matches)) {
			return !empty(trim($matches[1])) ? self::VISIBILITY_KEYWORDS[trim(strtolower($matches[1]))] : self::VISIBILITY_KEYWORDS['public'];
		}
		
		return self::VISIBILITY_KEYWORDS['public'];
	}	

	/**
	 * Get the name of a variable.
	 *
	 * Retrieves the name of a variable from the provided line.
	 *
	 * @param string $line The line to extract the variable name from.
	 * @param bool $throw Whether to throw an exception for invalid names.
	 * @return string|null The variable name or null if not found.
	 */
	public function getVariableName($line, bool $throw = true) {
		if (preg_match($this -> pattern, $line, $matches)) {
			$variableName = trim($matches[2]);

			if (!$this -> Namingrules -> isValid($variableName, $throw) && !$throw) {
				return null;
			}

			return $variableName;
		}
	
		return null;
	}

	/**
	 * Get the value of a variable.
	 *
	 * Retrieves the value of a variable from the provided line.
	 *
	 * @param string $line The line to extract the variable value from.
	 * @return string The variable value.
	 * @throws InvalidValueException If the value is invalid or not found.
	 */
	public function getVariableValue($line) {
		$pattern = '/(?:\=|\:)\s*(.*?)$/';
		
		if (preg_match($pattern, $line, $matches)) {
			if (trim($matches[1]) == '') {
				throw new InvalidValueException;
			}

			return trim($matches[1]);
		}

		throw new InvalidValueException;
	}

	/**
	 * Get the datatype of a variable.
	 *
	 * Retrieves the datatype of a variable from the provided line.
	 *
	 * @param string $line The line to extract the variable datatype from.
	 * @return string|null The variable datatype or null if not found.
	 */
	public function getVariableDatatype($line) {
		$pattern = '/\?\s*(.*?)(?:\=|;|:|$)/';
	
		if (preg_match($pattern, $line, $matches)) {
			return trim($matches[1]);
		}
	
		return null;
	}

	/**
	 * Get all details of a variable from the provided line.
	 *
	 * Retrieves visibility, name, datatype, and value of a variable from the line.
	 *
	 * @param string $line The line to extract variable details from.
	 * @return array Associative array containing variable details.
	 */
	public function getAllVariableDetails($line) {
		return array(
			'visibility' => $this -> getVariableVisibilitySyntax($line),
			'name' => $this -> getVariableName($line),
			'datatype' => $this -> getVariableDatatype($line),
			'value' => $this -> getVariableValue($line),
		);
	}

	/**
     * Change the name of a variable in a line.
     *
     * @param string $line The line containing the variable.
     * @param string $name The new name for the variable.
     * @return string The line with the updated variable name.
     */
	public function changeVariableName($line, $name): string {
		$oldname = $this -> getVariableName($line);
		if ($this -> getVariableDatatype($line)) {
			return preg_replace("/$oldname(?=\s*\?)/", $name, $line);
		}
		return preg_replace("/$oldname((?=.*?\:|.*?\=))/", $name, $line);
	}

	private function linePatternType($line, $name) {}
	
	/**
     * Change the datatype of a variable in a line.
     *
     * @param string $line The line containing the variable.
     * @param string $datatype The new datatype for the variable.
     * @return string The line with the updated variable datatype.
     */
	public function changeVariableDatatype($line, $datatype): string {
		$olddatatype = $this -> getVariableDatatype($line);
		$pattern = "/(?<=\?).*?(?=\s*\=|\s*\:)/";
		preg_match($pattern, $line, $matches);
		
		return preg_replace($pattern, str_replace(trim($olddatatype), trim($datatype), $matches[0]), $line);
	}

	/**
     * Change the value of a variable in a line.
     *
     * @param string $line The line containing the variable.
     * @param string $value The new value for the variable.
     * @return string The line with the updated variable value.
     */
	public function changeVariableValue($line, $value): string {
		$oldvalue = $this -> getVariableValue($line);
		$pattern = "/(?<=\=|\:).*/";
		preg_match($pattern, $line, $matches);

		return preg_replace($pattern, str_replace(trim($oldvalue), trim($value), $matches[0]), $line);
	}
}