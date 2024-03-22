<?php
namespace Novaxis\Core\Syntax\Datatype;

use Novaxis\Core\Error\ConversionErrorException;
use Novaxis\Core\Syntax\Datatype\TypesInterface;

/**
 * Represents the Boolean datatype.
 *
 * @package Novaxis\Core\Syntax\Datatype
 */
class BooleanType implements TypesInterface {
	/**
     * @var string $dataTypeName The name of the datatype (Boolean).
     */
	public $dataTypeName = 'Boolean';

	/**
     * @var mixed $value The value of the boolean variable.
     */
	private $value;

	/**
	 * An associative array mapping boolean representations (true and false) to their equivalent values.
	 * 
     * @var array $booleanValues
     */
	private $booleanValues = [
		"true" => ['true', '1'],
		"false" => ['false', '0']
	];

	/**
     * BooleanType constructor.
	 * 
     * Initializes the BooleanType object.
     */
	public function __construct() {
		
	}

	/**
     * Sets the value of the boolean variable.
     *
     * @param mixed $input The value to be set.
     * @return BooleanType This object instance.
     */
	public function setValue($input) {
		$this -> value = $input;
		
		return $this;
	}

	/**
     * Gets the current value of the boolean variable.
     *
     * @return mixed The value of the boolean variable.
     */
	public function getValue() {
		return $this -> value;
	}

	/**
     * Checks if the provided value is a valid boolean representation.
     *
     * @return bool True if the value is a valid boolean representation, false otherwise.
     */
	public function is() {
		foreach ($this -> booleanValues as $subBooleanValuesArray) {
			if (in_array(strtolower($this -> value), $subBooleanValuesArray)) {
				return true;
			}
		}
		
		return false;
	}

	/**
     * Converts the value to a boolean datatype.
     * Throws an exception if the value is not a valid boolean representation.
     *
	 * @return $this
     * @throws ConversionErrorException If the value is not a valid boolean representation.
     */
	public function convertTo() {
		if (!$this -> is()) {
			throw new ConversionErrorException;
		}

		foreach (array_keys($this -> booleanValues) as $subBooleanValuesArrayKeys) {
			if (in_array(strtolower($this -> value), $this -> booleanValues[$subBooleanValuesArrayKeys])) {
				$this -> value = var_export($subBooleanValuesArrayKeys === 'true' ? true : false, true);
			}
		}
		
		return $this;
	}
}