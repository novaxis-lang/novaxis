<?php
namespace Novaxis\Core\Syntax\Datatype;

use Novaxis\Core\Syntax\Datatype\TypesInterface;

class DictType implements TypesInterface {
	/**
	 * @var string $dataTypeName The name of the datatype (Byte).
	 */
	public $dataTypeName = 'Dict';

	public string $pattern = "";
	public mixed $value;

	public function setValue($input) {
		$this -> value = $input;
	}

	public function getValue(): mixed {
		return $this -> value;
	}

	public function is() {
		if (is_string($this -> value)) {
			json_decode($this -> value);
			return json_last_error() === JSON_ERROR_NONE;
		}
		return false;
	}
	
	public static function is_json() {
		return (new DictType) -> is();
	}

	public function convertTo() {}
}