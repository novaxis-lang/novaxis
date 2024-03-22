<?php
namespace Novaxis\Core\Syntax\Handler;

use Novaxis\Core\Syntax\Datatype\AutoType;
use Novaxis\Core\Syntax\Datatype\ByteType;
use Novaxis\Core\Syntax\Datatype\ListType;
use Novaxis\Core\Syntax\Datatype\NullType;
use Novaxis\Core\Syntax\Datatype\NumberType;
use Novaxis\Core\Syntax\Datatype\StringType;
use Novaxis\Core\Syntax\Datatype\BooleanType;
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
		'number' => NumberType::class,
		'string' => StringType::class,
		'boolean' => BooleanType::class,
		'list' => ListType::class,
		'null' => NullType::class,
		'none' => NullType::class,
		'byte' => ByteType::class,
		'auto' => AutoType::class,
	];

	/**
	 * An associative array that stores all connected types.
	 *
	 * @var array
	 */
	private array $allConnectedTypes;

	/**
	 * Connection to the current data type class instance.
	 *
	 * @var object|null
	 */
	private ?object $dataTypeClassConnect;

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
		$this -> allConnectedTypes = [];
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
		if (strstr(strtolower($datatype), 'auto')) {
			// if (!isset($this -> allConnectedTypes['auto'])) { }
			$this -> allConnectedTypes['auto'] = new $this -> dataTypeMap['auto']($this -> dataTypeMap);

			$this -> dataTypeClassConnect = $this -> allConnectedTypes['auto'];
			$this -> dataTypeClassConnect -> setDatatype($datatype);
		}
		else if (strstr(strtolower($datatype), 'list')) {
			if (!isset($this -> allConnectedTypes['list'])) {
				$this -> allConnectedTypes['list'] = new $this -> dataTypeMap['list']();
			}

			$this -> dataTypeClassConnect = $this -> allConnectedTypes['list'];
		}
		else {
			// Check if the datatype exists in the allConnectedTypes array, if not, create a new instance
			if (!in_array(strtolower($datatype), array_keys($this -> dataTypeMap))) {
				throw new InvalidDataTypeException;
			}
			
			if (!isset($this -> allConnectedTypes[$datatype])) {
				$this -> allConnectedTypes[$datatype] = new $this -> dataTypeMap[strtolower($datatype)]();
			}

			$this -> dataTypeClassConnect = $this -> allConnectedTypes[$datatype];
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