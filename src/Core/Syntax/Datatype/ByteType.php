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
	 * @var string $datatype The provided datatype string.
	 */
	private string $datatype = '';

	/**
	 * @var string $pattern The regex pattern for matching Byte as Hex, Binary, or Unit.
	 */
	public string $pattern = "/Byte\s*as\s*(Hex|Binary|Unit)/i";

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
	 * Set the datatype for the Byte.
	 *
	 * @param string $datatype The datatype to set.
	 * @return $this
	 */
	public function setDatatype(string $datatype) {
		$this -> datatype = $datatype;
		return $this;
	}

	/**
	 * Get the datatype of the Byte.
	 *
	 * @return string The datatype of the Byte.
	 */
	public function getDatatype() {
		return $this -> datatype;
	}

	/**
	 * Check if the provided datatype matches the pattern.
	 *
	 * @param string $datatype The datatype to match.
	 * @return bool True if the datatype matches, false otherwise.
	 */
	public function isMatchDatatype(string $datatype) {
		return preg_match($this -> pattern, $datatype);
	}

	/**
	 * Check if the provided datatype matches the pattern in a static context.
	 *
	 * @param string $datatype The datatype to match.
	 * @return bool True if the datatype matches, false otherwise.
	 */
	static function isMatchDatatypeStatic(string $datatype) {
		return preg_match(self::$pattern, $datatype);
	}
	
	/**
	 * Get the format of the datatype.
	 *
	 * @return string|null The format of the datatype, or null if not found.
	 */
	public function getFormat() {
		if (!empty($this -> datatype)) {
			preg_match($this -> pattern, $this -> datatype, $matches);
			if (!empty($matches[1])) {
				return trim($matches[1]);
			}
		}

		return null;
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
		return preg_match("/^(((\d+(\.\d+)?)(\s*[YZEPTGMKBp]{1,})|0x\s*[0-9A-Fa-f]+|0b\s*[01]+)(\W|\s){0,}?){0,}$/i", $this -> value);
		/* return (
			$this -> isValidBinValue($this -> value)
			|| $this -> isValidByteValue($this -> value)
			|| $this -> isValidHexValue($this -> value)
		); */
	}

	public function convertIntToUnits($integer) {
		if ($integer == 0) {
			return '0 B';
		}
		
		$factor = floor((strlen($integer) - 1) / 3);
		if ($factor >= 0 && $integer >= 1024) {
			$unit = array_search(pow(1024, $factor), $this -> multipliers);
		} else {
			$unit = 'B';
		}
		
		$value = $integer / $this -> multipliers[$unit];
		
		if (isset(explode(".", $value)[1]) && (int) explode(".", $value)[1] != 0) {
			return sprintf("%s %s", $this -> NumberType -> formatFloatAuto($value), $unit);
		}
		else {
			return sprintf("%d %s", $value, $unit);
		}	
	}

	public function convertIntToBinary($integer) {
		return '0b' . decbin($integer);
	}

	public function convertIntToHex($integer) {
		return '0x' . dechex($integer);
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

		$format = strtolower(($this -> getFormat()));
		if ($format == 'unit') {
			$this -> value = $this -> convertIntToUnits($this -> value);
		}
		else if ($format == 'binary') {
			$this -> value = $this -> convertIntToBinary($this -> value);
		}
		else if ($format == 'hex') {
			$this -> value = $this -> convertIntToHex($this -> value);
		}
		else {}


		return $this;
	}

	/**
	 * Check if the value is valid for Byte conversion.
	 *
	 * @param mixed $value The value to check.
	 * @return bool True if the value is valid, false otherwise.
	 */
	private function isValidByteValue($value) {
		return preg_match('/^((\d+(\.\d+)?)(\s*[YZEPTGMKBp]{1,})(\W|\s){0,}?){0,}$/i', $value);
	}

	/**
	 * Check if the value is a valid hexadecimal value.
	 *
	 * @param mixed $value The value to check.
	 * @return bool True if the value is valid, false otherwise.
	 */
	private function isValidHexValue($value) {
		return preg_match('/^(0x\s*[0-9A-Fa-f]+(\W|\s){0,}?){0,}$/', $value);
	}
	
	/**
	 * Check if the value is a valid binary value.
	 *
	 * @param mixed $value The value to check.
	 * @return bool True if the value is valid, false otherwise.
	 */
	private function isValidBinValue($value) {
		return preg_match('/^(0b\s*[01]+(\W|\s){0,}?){0,}$/', $value);
	}
	
	/**
	 * Parse a hexadecimal value to decimal.
	 *
	 * @param mixed $value The value to parse.
	 * @return string The parsed value.
	 */
	private function parseHexValue($value) {
		$value = preg_replace_callback('/0x\s*[0-9A-Fa-f]+/', function ($matches) {
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
		$value = preg_replace_callback('/0b\s*[01]+/', function ($matches) {
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
		$value = preg_replace_callback('/(\d+(\.\d+)?)(\s*[YZEPTGMKBp]{1,})/i', function ($matches) {
			$numericValue = floatval($matches[1]);
			$unit = strtoupper(trim($matches[3]));
	
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