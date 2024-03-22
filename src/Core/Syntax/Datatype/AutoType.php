<?php
namespace Novaxis\Core\Syntax\Datatype;

use Novaxis\Core\Syntax\Datatype\ListType;
use Novaxis\Core\Syntax\Datatype\NullType;
use Novaxis\Core\Syntax\Datatype\ByteType;
use Novaxis\Core\Syntax\Datatype\NumberType;
use Novaxis\Core\Syntax\Datatype\StringType;
use Novaxis\Core\Syntax\Datatype\BooleanType;
use Novaxis\Core\Syntax\Datatype\TypesInterface;
use Novaxis\Core\Error\AutotypeConversionException;

/**
 * Represents the Auto datatype.
 *
 * @package Novaxis\Core\Syntax\Datatype
 */
class AutoType implements TypesInterface {
	/**
	 * The name of the datatype (Auto).
	 *
	 * @var string
	 */
	public $dataTypeName = 'Auto';

	/**
	 * The provided datatype string.
	 *
	 * @var string
	 */
	private string $datatype = '';

	/**
	 * The value associated with the "auto" datatype.
	 *
	 * @var string
	 */
	private string $value;

	/**
	 * The selected datatype and value after auto-detection.
	 *
	 * @var array
	 */
	private array $item;

	/**
	 * An array containing all the supported datatypes for auto-detection.
	 *
	 * @var array
	 */
	private array $allTypes;

	/**
	 * An associative array that stores all connected types.
	 *
	 * @var array
	 */
	private array $allConnectedTypes;

	/**
	 * Regular expression pattern for detecting strings with an 'not' keyword.
	 *
	 * This pattern is used to identify strings containing the 'not' keyword.
	 *
	 * @var string
	 */
	private string $notContainsPattern = '/\(\s*not\s*([^)]+)\)/i';

	/**
	 * Regular expression pattern for detecting strings without a 'not' keyword (auto sure).
	 *
	 * This pattern is used to identify strings that don't contain the 'not' keyword, also known as "auto sure" strings.
	 *
	 * @var string
	 */
	private string $sureContainsPattern = '/\(\s*(?!not)([^)]+)\)/i';

	/**
	 * AutoType constructor.
	 *
	 * Initializes the AutoType class and sets up the supported datatypes.
	 */
	public function __construct(?array $dataTypes = []) {
		$this -> item = [];

		// if there is a "not" keyword in the value like this "auto (not boolean)",
		// removing the datatypes that are between () from $this -> allTypes variable
		$this -> allTypes = [
			"list"    => $dataTypes['list']    ?? ListType    :: class,
			"null"    => $dataTypes['null']    ?? NullType    :: class,
			"byte"    => $dataTypes['byte']    ?? ByteType    :: class,
			"number"  => $dataTypes['number']  ?? NumberType  :: class,
			"boolean" => $dataTypes['boolean'] ?? BooleanType :: class,
			"string"  => $dataTypes['string']  ?? StringType  :: class, // Always the last one
		];

		$this -> allConnectedTypes = [];
	}

	/**
	 * Set the datatype string associated with the "auto" datatype.
	 *
	 * @param string $datatype The datatype string.
	 * @return $this
	 */
	public function setDatatype($datatype) {
		$this -> datatype = $datatype;
		
		return $this;
	}

	/**
	 * Set the value associated with the "auto" datatype.
	 *
	 * @param mixed $input The value.
	 * @return $this
	 */
	public function setValue($input) {
		$this -> value = $input;

		return $this;
	}

	/**
	 * Get the value associated with the "auto" datatype.
	 *
	 * @return string The value.
	 */
	public function getValue() {
		return $this -> value;
	}

	/**
	 * Get the selected datatype and value after auto-detection.
	 *
	 * @return array The selected datatype and value in an array format.
	 */
	public function getItem() {
		return $this -> item;
	}

	/**
	 * Check if the "not" keyword exists in the datatype string.
	 *
	 * @return bool True if the "not" keyword exists, false otherwise.
	 */
	public function containsNotCode() {
		return preg_match($this -> notContainsPattern, $this -> datatype);
	}

	/**
	 * Get the datatypes specified after the "not" keyword.
	 *
	 * @return string The datatypes specified after "not" (e.g., "boolean, number").
	 */
	public function getNotDatatypes(){
		preg_match($this -> notContainsPattern, $this -> datatype, $matches);
		
		return isset($matches[1]) ? $matches[1] : '';
	}

	/**
	 * Remove the specified datatypes from consideration after "not" keyword.
	 */
	public function removeNotDatatypes() {
		$datatypes = explode(',', $this -> getNotDatatypes());

		foreach ($datatypes as $datatype) {
			$datatype = trim(strtolower($datatype));
			
			if (in_array($datatype, array_keys($this -> allTypes))) {
				unset($this -> allTypes[$datatype]);
			}
		}

		return $this;
	}

	/**
	 * Check if datatype contains "auto sure" code.
	 *
	 * @return bool True if "auto sure" code is present, false otherwise.
	 */
	public function containsSureCode() {
		return preg_match($this -> sureContainsPattern, $this -> datatype);
	}

	/**
	 * Get "auto sure" datatypes from the datatype string.
	 *
	 * @return string Extracted "auto sure" datatypes, or empty string if not found.
	 */
	public function getSureDatatypes() {
		preg_match($this -> sureContainsPattern, $this -> datatype, $matches);

		return isset($matches[1]) ? $matches[1] : '';
	}

	/**
	 * Select "auto sure" datatypes by filtering out non-matching datatypes from the list.
	 *
	 * @return $this
	 */
	public function selectSureDatatypes() {
		/* $datatypes = explode(',', $this -> getSureDatatypes());

		$array = array_diff(array_keys(array_map('strtolower', $this -> allTypes)), array_values(array_map(function($value) {
			return trim(strtolower($value));
		}, $datatypes)));
		
		foreach ($array as $datatype) {
			unset($this -> allTypes[trim($datatype)]);
		}

		return $this; */

		$datatypes = explode(',', $this -> getSureDatatypes());
		$datatypes = array_map(function($value) {
			return strtolower(trim($value));
		}, $datatypes);
		
		$newAllTypes = [];
		foreach ($datatypes as $datatype) {
			if (isset($this -> allTypes[$datatype])){
				$newAllTypes[$datatype] = $this -> allTypes[$datatype];
			}
		}

		$this -> allTypes = $newAllTypes;
		
		return $this;
	}

	/**
	 * Convert the input value to the appropriate datatype based on connected type classes.
	 *
	 * @return $this
	 * @throws AutotypeConversionException if conversion is not successful.
	 */
	public function convertTo() {
		if ($this -> containsNotCode()) {
			$this -> removeNotDatatypes();
		}
		else if ($this -> containsSureCode()) {
			$this -> selectSureDatatypes();
		}

		$this -> item = [];

		$input = trim($this -> value);

		foreach (array_keys($this -> allTypes) as $typeClassConnectKey) {
			if (!in_array($typeClassConnectKey, array_keys($this -> allConnectedTypes))) {
				$this -> allConnectedTypes[$typeClassConnectKey] = new $this -> allTypes[$typeClassConnectKey]();
			}

			$typeClassConnected = $this -> allConnectedTypes[$typeClassConnectKey];
			$typeClassConnected -> setValue($input);

			if ($typeClassConnected -> is()) {
				$this -> item = array(
					'datatype' => $typeClassConnected -> dataTypeName,
					'value' => $typeClassConnected -> convertTo() -> getValue()
				);
				
				break;
			}
		}
		
		if (empty($this -> item)) {
			throw new AutotypeConversionException;
		}

		return $this;
	}
}