<?php
namespace Novaxis\Core\Syntax\Datatype;

use Novaxis\Core\Syntax\Datatype\TypesConf;
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
	 * The configuration for datatypes.
	 *
	 * @var TypesConf
	 */
	private TypesConf $TypesConf;

	/**
	 * AutoType constructor.
	 *
	 * Initializes the AutoType class and sets up the supported datatypes.
	 */
	public function __construct() {
		$this -> item = [];
		$this -> TypesConf = new TypesConf;
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
	public function getNotDatatypes() {
		preg_match($this -> notContainsPattern, $this -> datatype, $matches);
		
		return isset($matches[1]) ? $matches[1] : '';
	}

	public function splitNotDatatypes(): array {
		$datatypes = explode(',', $this -> getNotDatatypes());
		return $datatypes;
	}

	/**
	 * Remove the specified datatypes from consideration after "not" keyword.
	 */
	public function removeNotDatatypes() {
		foreach ($this -> splitNotDatatypes() as $datatype) {
			$datatype = trim(strtolower($datatype));
			$this -> TypesConf -> unset_datatype($datatype);
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

	public function splitSureDatatypes(): array {
		$datatypes = explode(',', $this -> getSureDatatypes());
		$datatypes = array_map(function($value) {
			return strtolower(trim($value));
		}, $datatypes);
		return $datatypes;
	}

	/**
	 * Select "auto sure" datatypes by filtering out non-matching datatypes from the list.
	 *
	 * @return $this
	 */
	public function selectSureDatatypes() {
		$this -> TypesConf -> retain_datatypes($this -> splitSureDatatypes());
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
		$datatype = $this -> TypesConf -> uni_value_which($input);
		$datatype_str = null;

		/* datatype matches */
		$dFits = $this -> TypesConf -> which_fits($this -> splitSureDatatypes(), $input);
		if (!empty($dFits)) {
			$datatype = $dFits[0][0];
			$datatype_str = $dFits[0][1];
		}

		if ($datatype) {
			$datatype_connection = $this -> TypesConf -> fit_datatype($datatype); // If not found, an exception will be displayed. 
			$datatype_connection -> setValue($input);
			if (method_exists($datatype_connection, 'setDatatype')) {
				$datatype_connection -> setDatatype($datatype_str ?? $datatype);
			}
			$this -> item = array(
				"datatype" => $this -> TypesConf -> getDataTypeNameByKey($datatype),
				"value" => $datatype_connection -> convertTo() -> getValue()
			);
		}

		$this -> TypesConf -> reset();

		if (empty($this -> item)) {
			throw new AutotypeConversionException;
		}

		return $this;
	}

	/**
	 * Placeholder method to check if a datatype is valid.
	 */
	public function is() {}
}
