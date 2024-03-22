<?php
namespace Novaxis\Core\Syntax\Handler;
use Novaxis\Core\Syntax\Handler\Namingrules;

/**
 * The ClassHandler class handles operations related to class syntax and tokens.
 */
class ClassHandler {
	/**
	 * Regular expression pattern for matching and parsing variable declarations.
	 *
	 * @var string
	 */
	private $pattern = '/^\s*(([^=\s:->\?]+))\s*(?:\s*\?\s*([^=:\n]*?(?:\([^)]*\))?(?:\s+[^=:\n]*?(?:\([^)]*\))?)*))?\s*(?:->\s*(\d+|(\{(\w|\W){0,}\})))?\s*$/i';
	//NOTE - ([^=\s:->]+) -> ([^=\s:->\?]+)

	/**
	 * Instance of the Namingrules class for validating naming rules.
	 *
	 * @var Namingrules
	 */
	private Namingrules $Namingrules;

	/**
	 * Constructor for the classHandler class.
	 */
	public function __construct() {
		$this -> Namingrules = new Namingrules;
	}

	/**
	 * Checks if the given input represents a class declaration.
	 *
	 * @param string $input The input to check.
	 * @return bool True if the input represents a class declaration, false otherwise.
	 */
	public function isClass($input) {
		preg_match($this -> pattern, $input, $matches);
		return isset($matches[2]) && $this -> Namingrules -> isValid(trim($matches[2]), false);
	}

	/**
	 * Check if the input string represents a class box with datatype.
	 *
	 * @param string $input The input string to check.
	 * @return bool True if the input is a valid class box with datatype, otherwise false.
	 */
	public function isClassBox($input) {
		$pattern = '/^\s*\?\s*((\w|\W){0,})\s*$/';
		
		return preg_match($pattern, $input);
	}
	
	/**
	 * Get the datatype from a class box representation.
	 *
	 * @param string $input The input string containing the class box representation.
	 * @return string|null The extracted datatype if found, or null if the format is invalid.
	 */
	public function getClassBox($input) {
		$pattern = '/^\s*\?\s*((\w|\W){0,})\s*$/';
		preg_match($pattern, $input, $matches);

		return trim($matches[1]);
	}

	/**
	 * Extracts the class name from the given input.
	 *
	 * @param string $input The input to extract the class name from.
	 * @return string|null The name of the class, or null if not found.
	 * @throws 'NamingRuleException' If the extracted class name is invalid according to naming rules.
	 */
	public function getClassName($input) {
		if (preg_match($this -> pattern, $input, $matches)) {
			$this -> Namingrules -> isValid($matches[2], true);
			
			return $matches[2];
		}
	
		return null;
	}

	/**
	 * Extracts the datatype of the class from the given input.
	 *
	 * @param string $input The input to extract the class datatype from.
	 * @return string|null The datatype of the class, or null if not found.
	 */
	public function getClassDatatype($input) {
		if (preg_match($this -> pattern, $input, $matches)) {
			return $matches[3] ?? null;
		}

		return null;
	}

	/**
	 * Get the maximum number from the given syntax.
	 *
	 * @param string $line The line string to extract the maximum number from.
	 * @return string The extracted maximum number or an empty string if not found.
	 */
	public function getMaximumNumber(string $line): string {
		$pattern = '/\s*->\s*(\d+|(\{(\w|\W){0,}\}))\s*$/';
		preg_match($pattern, $line, $matches);
		
		return isset($matches[1]) ? $matches[1] : '';
	}
	
	/**
	 * Check if the given syntax contains a maximum number definition.
	 *
	 * This function checks if the provided line of syntax contains a maximum number definition.
	 *
	 * @param string $line The line string to check for a maximum number definition.
	 * @return bool True if a maximum number is found, false otherwise.
	 */
	public function hasMaximumNumber(string $line): bool {
		$pattern = '/\s*->\s*(\d+|(\{(\w|\W){0,}\}))\s*$/';
		
		return preg_match($pattern, $line);
	}

	// public function cases() {}
	
	/**
	 * Change the class name in the given line.
	 *
	 * @param string $line The input line.
	 * @param string $name The new class name.
	 *
	 * @return string The modified line with the new class name.
	 */
	public function changeClassName(string $line, string $name): string {
		$oldname = $this -> getClassName($line);
		return preg_replace("/$oldname(?=(\s*\?|\s*\->))/", $name, $line);
	}

	/**
	 * Change the class datatype in the given line.
	 *
	 * @param string $line The input line.
	 * @param string $datatype The new class datatype.
	 *
	 * @return string The modified line with the new class datatype.
	 */
	public function changeClassDatatype(string $line, string $datatype): string {
		$olddatatype = $this -> getClassDatatype($line);
		if ($this -> hasMaximumNumber($line)) {
			$pattern = "/(?<=\?).*?(?=\s*\-\>)/";
		}
		else {
			$pattern = "/(?<=\?).*?/";
		}

		preg_match($pattern, $line, $matches);
		return preg_replace($pattern, str_replace(trim($olddatatype), trim($datatype), $matches[0]), $line);
	}

	/**
     * Change the classbox datatype in the given line.
     *
     * @param string $line The input line.
     * @param string $datatype The new classbox datatype.
     *
     * @return string The modified line with the new classbox datatype.
     */
	public function changeClassboxDatatype(string $line, string $datatype): string {
		$olddatatype = $this -> getClassBox($line);
		$pattern = "/(?<=\?)(.{0,})?/";
		preg_match($pattern, $line, $matches);
		return preg_replace($pattern, str_replace(trim($olddatatype), trim($datatype), $matches[0]), $line);
	}
}