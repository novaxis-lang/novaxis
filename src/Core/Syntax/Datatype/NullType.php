<?php
namespace Novaxis\Core\Syntax\Datatype;

use Novaxis\Core\Error\ConversionErrorException;
use Novaxis\Core\Syntax\Datatype\TypesInterface;

/**
 * Class NullType
 * 
 * Represents the Null datatype in Novaxis.
 *
 * @package Novaxis\Core\Syntax\Datatype
 */
class NullType implements TypesInterface {
    /**
     * @var string The name of the data type.
     */
    public $dataTypeName = 'Null';

    /**
     * @var array An array of possible null values.
     */
	private $nullValues = ['none', 'null'];

    /**
     * @var null|string The value of the NullType instance.
     */
    private ?string $value;

    /**
     * NullType constructor.
     * 
     * Initializes a new NullType instance.
     */
    public function __construct() {

    }

    /**
     * Sets the input value for the NullType instance.
     *
     * @param mixed $input The input value.
     * 
     * @return $this
     */
    public function setValue($input) {
        $this -> value = $input;

        return $this;
    }

    /**
     * Gets the value of the NullType instance.
     *
     * @return null|string The value of the NullType instance.
     */
    public function getValue() {
        return $this -> value;
    }

    /**
     * Checks if the current value is a valid representation of null/none.
     *
     * @return bool True if the value is a valid null/none representation, false otherwise.
     */
	public function is() {
		return in_array(strtolower($this -> value), $this -> nullValues);
    }

    /**
     * Converts the current value to a proper representation of null.
     *
     * @return $this The current NullType instance.
     * @throws ConversionErrorException If the current value is not a valid null/none representation.
     */
    public function convertTo() {
        if ($this -> is()) {
            $this -> value = var_export(null, true);
        } else {
            throw new ConversionErrorException;
        }

        return $this;
    }
}