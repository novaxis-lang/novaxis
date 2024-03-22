<?php
namespace Novaxis\Core\Syntax\Handler\Variable;

use Novaxis\Core\Syntax\Token\PathTokens;
use Novaxis\Core\Syntax\Datatype\ListType;
use Novaxis\Core\Syntax\Datatype\NumberType;
use Novaxis\Core\Syntax\Token\VariableTokens;
use Novaxis\Core\Error\VariableInterpolationException;
use Novaxis\Core\Error\InterpolationPathNotFoundException;
use Novaxis\Core\Syntax\Handler\Variable\VisibilitySyntax;

/**
 * Class Interpolation
 *
 * This class handles variable interpolation in the input string.
 * 
 * @package Novaxis\Core\Syntax\Handler\Variable
 */
class Interpolation {
	use VariableTokens;
	use PathTokens;

	/**
	 * @var string The regex pattern for detecting interpolated variables in the input string.
	 */
	private $pattern = '/(?<!\\\\)(\\\\\\\\)*\{[^{}\\\\]*(?:\\\\.[^{}\\\\]*)*[^{}\\\\]*\}(?!\\\\)/';

	/**
	 * Instance of the ListType class for handling list-related operations.
	 *
	 * @var ListType
	 */
	private ListType $ListType;

	/**
	 * @var NumberType Holds an instance of the NumberType class.
	 */
	private NumberType $NumberType;
	
	/**
	 * Instance of the VisibilitySyntax class for managing variable visibility styles.
	 *
	 * @var VisibilitySyntax
	 */
	private VisibilitySyntax $VisibilitySyntax;

	/**
	 * Interpolation constructor.
	 */
	public function __construct() {
		$this -> ListType = new ListType;
		$this -> NumberType = new NumberType;
		$this -> VisibilitySyntax = new VisibilitySyntax;
	}
	
	/**
	 * Check if the input string has any variable interpolation.
	 *
	 * @param string $input The input string to check.
	 * @return bool Returns true if the input string contains variable interpolation, otherwise false.
	 */
	public function hasInterpolation($input) {
		return is_string($input) ? (bool) preg_match($this -> pattern, $input) : false;
	}
	
	/**
	 * Get an array of all variable interpolations in the input string.
	 *
	 * @param string $input The input string to search for variable interpolations.
	 * @return array An array containing all variable interpolations found in the input string.
	 */
	public function getInterpolations($input) {
		preg_match_all($this -> pattern, $input, $matches);

		return $matches[0];
	}
	
	/**
	 * Remove the braces from an interpolated variable.
	 *
	 * @param string $input The interpolated variable with braces.
	 * @return string The interpolated variable without braces.
	 */
	public function removeBraces($input) {
		return trim($input, '\\{}');
	}

	/**
	 * Replace interpolated variables in the input string with corresponding values from the given JSON data.
	 *
	 * @param string $input The input string containing interpolated variables.
	 * @param array $jsonData An associative array of JSON data containing the values of the variables.
	 * @param string|null $basePath Optional base path to resolve relative paths in variable names.
	 * @return string The input string with all interpolated variables replaced with their values.
	 * @throws InterpolationPathNotFoundException If the specified path does not exist in the JSON data.
	 */
	public function replaceValue(string $input, array $jsonData, ?string $basePath = null, string $order = 'value'): string {
		$order = trim(strtolower($order));
		if (!in_array(trim(strtolower($order)), array('value', 'datatype'))) {
			$order = 'value';
		}
		
		$variableCount = substr_count($input, self::INTERPOLATION_OPEN[0]); // edit this
		$tempInput = $input;

		foreach (range(0, $variableCount) as $_){
			$input = preg_replace_callback($this -> pattern, function ($match) use ($jsonData, $basePath, $tempInput, $order) {
				if ($this -> NumberType -> isMathematicalOperation($this -> removeBraces($match[0]))) {
					return $this -> NumberType -> calculateResult($this -> removeBraces($match[0]));
				}

				$variable = str_replace(' ', '', trim($this -> removeBraces($match[0])));
				$currentVariable = $variable;
				
				if (count(explode(self::PATH_SEPARATOR, $currentVariable)) > 1 && $basePath) {
					if (explode(self::PATH_SEPARATOR, $currentVariable)[1] === 'self' && $currentVariable[0] === '.') {
						$currentVariable = ($basePath) . (self::PATH_SEPARATOR . implode(self::PATH_SEPARATOR, array_slice(explode(self::PATH_SEPARATOR, $currentVariable), 2)));
					}
				}

				
				if (!isset($jsonData[$currentVariable][$order]) || !isset($jsonData[$currentVariable]['visibility'])) {
					throw new InterpolationPathNotFoundException("The specified path '{$currentVariable}' does not exist in the data structure.");
				}
				
				$currentVisibility = $jsonData[$currentVariable]['visibility'];

				if (!$this -> VisibilitySyntax -> fit($currentVisibility, $currentVariable, $basePath, true)) {
					throw new VariableInterpolationException;
				}

				if (is_array($jsonData[$currentVariable][$order])) {
					$value = $this -> ListType -> arrayToString($jsonData[$currentVariable][$order]);

					if (count($jsonData[$currentVariable][$order]) >= 2) {
						$value = $this -> ListType -> removeFirstAndLastLetter($value);
					}
				} else {
					$value = strval($jsonData[$currentVariable][$order]);
				}


				return explode('{', $match[0])[0] . $value . explode('}', $match[0])[1];
			}, $input);
		}

		return $input;
	}

	/**
	 * Execute variable interpolation in the given value.
	 *
	 * This function checks if variable interpolation is present in the provided value. If it is, it replaces the interpolated variables with their corresponding values.
	 *
	 * @param string $value The value string to check and perform interpolation on.
	 * @param array $items An array of items representing variables and their values.
	 * @param string $path The current path or context for interpolation.
	 * @return string|false The interpolated value if interpolation is present, or false if there's no interpolation.
	 */
	public function execute(string $value, array $items, string $path) {
		if ($this -> hasInterpolation($value)) {
			return $this -> replaceValue($value, $items, $path);
		}

		return false;
	}
}