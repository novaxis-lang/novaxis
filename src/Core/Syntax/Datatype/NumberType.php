<?php
namespace Novaxis\Core\Syntax\Datatype;

use Novaxis\Core\Error\ConversionErrorException;
use Novaxis\Core\Syntax\Datatype\TypesInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Class NumberType
 * 
 * Represents the Number datatype in Novaxis.
 *
 * @package Novaxis\Core\Syntax\Datatype
 */
class NumberType implements TypesInterface {
	/**
	 * @var string The name of the data type.
	 */
	public $dataTypeName = 'Number';

	/**
	 * The value of the NumberType instance.
	 * 
	 * @var int|float|string
	 */
	private $value;

	/**
	 * NumberType constructor.
	 * 
	 * Initializes a new NumberType instance.
	 */
	public function __construct() {}
	
	/**
	 * Sets the input value for the NumberType instance.
	 *
	 * @param mixed $input The input value.
	 * @return $this The current NumberType instance.
	 */
	public function setValue($input) {
		$this -> value = $input;

		return $this;
	}

	/**
	 * Gets the value of the NumberType instance.
	 *
	 * @return int|float The value of the NumberType instance.
	 */
	public function getValue() {
		return $this -> value;
	}
	
	/**
	 * Checks if the current value is a valid representation of a number or a valid mathematical expression.
	 *
	 * @return bool True if the value is a valid number representation, a valid mathematical expression, or a boolean value, false otherwise.
	 */
	public function is() {
		if (is_string($this -> value)) {
			if ($this -> value[0] == '"' && substr($this -> value, -1) == '"') {
				return false;
			}

			$this -> value = strtolower($this -> value);
		}
		
		if (is_numeric($this -> value) || $this -> isMathematicalOperation() || is_bool($this -> value)) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the given input or the current value is a valid mathematical operation.
	 *
	 * @param string|null $input The input string to evaluate as a mathematical operation.
	 * @return bool True if the input or current value is a valid mathematical operation, false otherwise.
	 */
	public function isMathematicalOperation($input = null): bool {
		$expressionLanguage = new ExpressionLanguage();

		try {
			$result = $expressionLanguage -> evaluate(strtolower($input ?? $this -> value));
		} catch (\Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * Calculates the result of a mathematical expression using the ExpressionLanguage component.
	 *
	 * @param string|null $input The mathematical expression to evaluate. If null, uses the stored value.
	 * @return float|int|null The calculated result of the expression, or null if the expression is invalid.
	 */
	public function calculateResult($input = null) {
		if ($input) {
			$input = strtolower($input);
		} else if ($this -> value) {
			$this -> value = strtolower($this -> value);
		}

		if (!$this -> isMathematicalOperation($input)) {
			return null;
		}

		$expressionLanguage = new ExpressionLanguage();

		try {
			$result = $expressionLanguage -> evaluate($input ?? $this -> value);
		} catch (\Exception $e) {
			return null;
		}

		return $result;
	}

	/**
	 * Converts the current value to a proper number representation.
	 *
	 * @return $this
	 * @throws ConversionErrorException If the current value is not a valid number representation.
	 */
	public function convertTo() {
		if ($this -> isMathematicalOperation()) {
			$this -> value = $this -> calculateResult();
		}
		if (!$this -> is()) {
			throw new ConversionErrorException;
		}

		if (strpos($this -> value, '.')) {
			$this -> value = (float) $this -> value;
		} else {
			$this -> value = (int) $this -> value;
		}
		
		return $this;
	}
}