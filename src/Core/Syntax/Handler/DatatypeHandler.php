<?php
namespace Novaxis\Core\Syntax\Handler;

use Novaxis\Core\Syntax\Datatype\TypesConf;
use Novaxis\Core\Error\InvalidDataTypeException;
use Novaxis\Core\Syntax\Handler\Variable\Interpolation;

/**
 * The DatatypeHandler class handles operations related to different data types.
 */
class DatatypeHandler {
	/**
	 * Map of data type names to their corresponding classes.
	 *
	 * @var array
	 */
	private $dataTypeMap = [
		'number',
		'string',
		'boolean',
		'list',
		'null',
		'byte',
		'auto',
	];

	/**
	 * Connection to the current data type class instance.
	 *
	 * @var object|null
	 */
	private ?object $dataTypeClassConnect;

	/**
	 * The configuration for datatypes.
	 *
	 * @var TypesConf
	 */
	private TypesConf $TypesConf;

	/**
	 * Interpolation instance for handling variable interpolation.
	 *
	 * @var Interpolation
	 */
	private Interpolation $Interpolation;

	/**
	 * Constructor for the DatatypeHandler class.
	 */
	public function __construct() {
		$this -> TypesConf = new TypesConf;
		$this -> Interpolation = new Interpolation;
	}

	/**
	 * Create a new datatype object and set its value.
	 *
	 * @param string $datatype The datatype to create.
	 * @param mixed $value The value to set for the datatype.
	 * @return $this
	 * @throws InvalidDataTypeException If the specified datatype is invalid or not supported.
	 */
	public function createDatatype(string $datatype, mixed $value) {
		if ($this -> TypesConf -> conf["AutoType"]["is_datatype"]($datatype)) {
			$this -> dataTypeClassConnect = $this -> TypesConf -> fit_datatype("AutoType");
			$this -> dataTypeClassConnect -> setDatatype($datatype);
		}
		else if ($this -> TypesConf -> conf["ByteType"]["is_datatype"]($datatype)) {
			$this -> dataTypeClassConnect = $this -> TypesConf -> fit_datatype("ByteType");
			$this -> dataTypeClassConnect -> setDatatype($datatype);
		}
		else {
			// Check if the datatype exists in the allConnectedTypes array, if not, create a new instance
			if (!in_array(strtolower($datatype), $this -> dataTypeMap)) {
				throw new InvalidDataTypeException;
			}
			
			$this -> dataTypeClassConnect = $this -> TypesConf -> fit_datatype($this -> TypesConf -> getKeyByDataTypeName($datatype));
		}

		// Set value and convert the datatype
		$this -> dataTypeClassConnect -> setValue($value);
		$this -> dataTypeClassConnect -> convertTo();

		return $this;
	}

	/**
	 * Perform datatype interpolation if needed.
	 *
	 * Checks for interpolation in the datatype and replaces values if present.
	 *
	 * @param string $datatype The initial datatype.
	 * @param array $jsonData The JSON data for interpolation.
	 * @param string $basePath The base path for resolving interpolation.
	 * @return string The updated datatype after interpolation.
	 */
	public function datatypeInterpolation(?string $datatype = null, array $jsonData, string $basePath) {
		if ($datatype === null) {
			throw new InvalidDataTypeException;
		}
		if ($this -> Interpolation -> hasInterpolation($datatype)) {
			$datatype = $this -> Interpolation -> replaceValue($datatype, $jsonData, $basePath, 'datatype');
		}

		return $datatype;
	}

	/**
	 * Gets the value of the current datatype class instance.
	 *
	 * @return mixed The value of the current datatype.
	 */
	public function getValue() {
		return $this -> dataTypeClassConnect -> getValue();
	}

	/**
	 * Gets the datatype of the current datatype class instance.
	 *
	 * @return string The datatype name.
	 */
	public function getDatatype() {
		return $this -> dataTypeClassConnect -> dataTypeName;
	}

	/**
	 * Gets the connection to the current datatype class instance.
	 *
	 * @return object The connection to the current datatype class.
	 */
	public function getDatatypeConnection() {
		return $this -> dataTypeClassConnect;
	}
}