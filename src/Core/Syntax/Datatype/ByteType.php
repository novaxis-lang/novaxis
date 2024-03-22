<?php
namespace Novaxis\Core\Syntax\Datatype;

use Novaxis\Core\Syntax\Datatype\NumberType;
use Novaxis\Core\Error\ConversionErrorException;
use Novaxis\Core\Syntax\Datatype\TypesInterface;

/**
 * Represents the Byte datatype.
 *
 * @package Novaxis\Core\Syntax\Datatype
 */
class ByteType implements TypesInterface {
	/**
	 * @var string $dataTypeName The name of the datatype (Byte).
	 */
	public $dataTypeName = 'Byte';

	/**
	 * Multipliers for converting different byte units to bytes.
	 *
	 * This array contains multipliers for various byte units (e.g., KB, MB, GB) to convert them to bytes. The keys represent the unit, and the values represent the corresponding multipliers for the conversion.
	 *
	 * @var array
	 */
	public $multipliers = [
		'B' => 1,
		'KB' => 1024,
		'MB' => 1024 * 1024,
		'GB' => 1024 * 1024 * 1024,
		'TB' => 1024 * 1024 * 1024 * 1024,
		'PB' => 1024 * 1024 * 1024 * 1024 * 1024,
		'EB' => 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
		'ZB' => 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
		'YB' => 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
	];

	/**
	 * @var mixed $value The value of the Byte.
	 */
	private $value;

	/**
	 * @var $NumberType The number type.
	 */
	private NumberType $NumberType;

	/**
	 * The constructor for the ByteType class.
	 */
	public function __construct() {
		$this -> NumberType = new NumberType;
	}

	/**
	 * Set the value of the Byte.
	 *
	 * @param mixed $value The value of the Byte.
	 * @return $this
	 */
	public function setValue($value) {
		$this -> value = $value;

		return $this;
	}

	/**
	 * Get the value of the Byte.
	 *
	 * @return mixed The value of the Byte.
	 */
	public function getValue() {
		return $this -> value;
	}

	/**
	 * Check if the value is valid for ByteType conversion.
	 *
	 * @return bool True if the value is valid, false otherwise.
	 */
	public function is() {
		return preg_match("/^(((\d+(\.\d+)?)([YZEPTGMKBp]{1,})|0x[0-9A-Fa-f]+|0b[01]+)(\W|\s){0,}?){0,}$/", $this -> value);
		/* return (
			$this -> isValidBinValue($this -> value)
			|| $this -> isValidByteValue($this -> value)
			|| $this -> isValidHexValue($this -> value)
		); */
	}

	/**
	 * Convert the value to the appropriate format.
	 *
	 * @return ByteType This instance with the converted value.
	 * @throws ConversionErrorException If the value is not valid.
	 */
	public function convertTo() {
		if (!$this -> is()) {
			throw new ConversionErrorException;
		}

		$this -> value = $this -> parseHexValue($this -> value);
		$this -> value = $this -> parseBinValue($this -> value);
		$this -> value = $this -> parseByteValue($this -> value);

		if ($this -> NumberType -> isMathematicalOperation($this -> value)) {
			$this -> value = $this -> NumberType -> calculateResult($this -> value);
		}

		return $this;
	}

	/**
	 * Check if the value is valid for Byte conversion.
	 *
	 * @param mixed $value The value to check.
	 * @return bool True if the value is valid, false otherwise.
	 */
	private function isValidByteValue($value) {
		return preg_match('/^((\d+(\.\d+)?)([YZEPTGMKBp]{1,})(\W|\s){0,}?){0,}$/i', $value);
	}

	/**
	 * Check if the value is a valid hexadecimal value.
	 *
	 * @param mixed $value The value to check.
	 * @return bool True if the value is valid, false otherwise.
	 */
	private function isValidHexValue($value) {
		return preg_match('/^(0x[0-9A-Fa-f]+(\W|\s){0,}?){0,}$/', $value);
	}
	
	/**
	 * Check if the value is a valid binary value.
	 *
	 * @param mixed $value The value to check.
	 * @return bool True if the value is valid, false otherwise.
	 */
	private function isValidBinValue($value) {
		return preg_match('/^(0b[01]+(\W|\s){0,}?){0,}$/', $value);
	}
	
	/**
	 * Parse a hexadecimal value to decimal.
	 *
	 * @param mixed $value The value to parse.
	 * @return string The parsed value.
	 */
	private function parseHexValue($value) {
		$value = preg_replace_callback('/0x[0-9A-Fa-f]+/', function ($matches) {
			$hex = $matches[0];
			$decimal = hexdec($hex);
			return $decimal;
		}, $value);

		return $value;
	}

	/**
	 * Parse a binary value to decimal.
	 *
	 * @param mixed $value The value to parse.
	 * @return string The parsed value.
	 */
	private function parseBinValue($value) {
		$value = preg_replace_callback('/0b[01]+/', function ($matches) {
			$binary = $matches[0];
			$decimal = bindec($binary);
			return $decimal;
		}, $value);

		return $value;
	}

	/**
	 * Parse a byte value with units to bytes.
	 *
	 * @param mixed $value The value to parse.
	 * @return string The parsed value in bytes.
	 */
	private function parseByteValue($value) {
		$value = preg_replace_callback('/(\d+(\.\d+)?)([YZEPTGMKBp]{1,})/i', function ($matches) {
			$numericValue = floatval($matches[1]);
			$unit = strtoupper($matches[3]);
	
			$decimal = null;
			if (isset($this -> multipliers[$unit])) {
				$decimal = $numericValue * $this -> multipliers[$unit];
			}
			$decimal = $decimal ?? $numericValue;
	
			if (str_contains($decimal, '.')) {
				return (float) $decimal;
			} else {
				return (int) $decimal;
			}
		}, $value);

		return $value;
	}
}