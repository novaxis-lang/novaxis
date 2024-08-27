<?php
namespace Novaxis\Core\Syntax\Datatype;

use Novaxis\Core\Syntax\Datatype\NumberType;
use Novaxis\Core\Error\DataTypeNotFoundException;

class TypesConf {
	public array $conf;

	private NumberType $NumberType;

	public function __construct() {
		$this -> NumberType = new NumberType();

		$this -> conf = [
			"AutoType" => [
				"is_datatype" => function (?string $datatype): bool {
					return preg_match("/^auto\s*(\(\s*([^)]+)\))?$/i", trim($datatype));
				},
				"dataTypeName" => "Auto",
				"datatype_class" => AutoType::class
			],
			"ByteType" => [
				"is" => function (string $value): bool {
					return preg_match("/^(((\d+(\.\d+)?)(\s*[TGMKBp]{1,})|0x\s*[0-9A-Fa-f]+|0b\s*[01]+)(\W|\s){0,}?){1,}$/i", trim($value));
				},
				"is_datatype" => function (?string $datatype): bool {
					return preg_match("/^Byte\s*as\s*(Hex|Binary|Unit)|Byte$/i", trim($datatype));
				},
				"dataTypeName" => "Byte",
				"datatype_class" => ByteType::class
			],
			"ListType" => [
				"is" => function (string $value): bool {
					$value = trim($value);
					return (substr($value, 0, 1) === '[' && substr($value, -1) === ']');
				},
				"is_datatype" => function (?string $datatype): bool {
					return !empty(strstr(strtolower($datatype), "list"));
				},
				"dataTypeName" => "List",
				"datatype_class" => ListType::class
			],
			"NullType" => [
				"is" => function (string $value): bool {
					return in_array(strtolower(trim($value)), ['none', 'null']);
				},
				"dataTypeName" => "Null",
				"datatype_class" => NullType::class
			],
			"NumberType" => [
				"is" => function (string $value): bool {
					return $this -> NumberType -> setValue($value) -> is();
				},
				"dataTypeName" => "Number",
				"datatype_class" => NumberType::class
			],
			"BooleanType" => [
				"is" => function (string $value): bool {
					foreach ([
						"true" => ['true', '1'],
						"false" => ['false', '0']
					] as $subBooleanValuesArray) {
						if (in_array(strtolower(trim($value)), $subBooleanValuesArray)) {
							return true;
						}
					}
					
					return false;
				},
				"dataTypeName" => "Boolean",
				"datatype_class" => BooleanType::class
			],
			"StringType" => [
				"is" => function (string $value): bool {
					$value = trim($value);
					return is_string($value) && $value !== '' && $value[0] === '"' && substr($value, -1) === '"';
				},
				"dataTypeName" => "String",
				"datatype_class" => StringType::class
			],
		];
	}

	public function getKeyByDataTypeName(string $dataTypeName): ?string {
		foreach ($this -> conf as $key => $value) {
			if (isset($value['dataTypeName']) && strtolower($value['dataTypeName']) === strtolower($dataTypeName)) {
				return $key;
			}
		}
		return null;
	}

	public function getDataTypeNameByKey(string $key): ?string {
		foreach ($this -> conf as $confKey => $confValue) {
			if (strcasecmp($confKey, $key) === 0) {
				return $confValue['dataTypeName'] ?? null;
			}
		}
		return null;
	}

	public function retain_datatypes(array $datatypes): void {
		$datatypes_keys = array_map(['self', 'getKeyByDataTypeName'], $datatypes);
		$conf = [];

		foreach ($datatypes_keys as $i => $datatype_key) {
			if (array_key_exists($datatype_key, $this -> conf)) {
				$conf[$datatype_key] = $this -> conf[$datatype_key];
			}
			else if ($this -> conf["ByteType"]["is_datatype"]($datatypes[$i]) == true) {
				$conf["ByteType"] = $this -> conf["ByteType"];
			}
		}

		$this -> conf = $conf;
	}

	public function not_datatype(array $datatypes, array $not): array {
		$loweredNot = array_map('strtolower', $not);

		return array_values(array_filter($datatypes, function ($single_datatype) use ($loweredNot) {
			return !in_array(strtolower($single_datatype), $loweredNot);
		}));
	}

	public function value_which(string $value): array {
		return array_keys(array_filter($this -> conf, function($item) use ($value) {
			return isset($item["is"]) && $item["is"]($value) === true;
		}));
	}

	public function uni_value_which(string $value): ?string {
		return $this -> value_which($value)[0] ?? null;
	}

	public function datatype_which(string $datatype): array {
		return array_keys(array_filter($this -> conf, function($item) use ($datatype) {
			return isset($item["is_datatype"]) && $item["is_datatype"]($datatype) === true;
		}));
	}

	public function uni_datatype_which(string $datatype): ?string {
		return $this -> datatype_which($datatype)[0] ?? null;
	}

	public function fit_datatype(string $datatype): object {
		if (!isset($this -> conf[$datatype])) {
			throw new DataTypeNotFoundException("The datatype '$datatype' does not exist in the configuration.");
		}
		$datatype_class = $this -> conf[$datatype]["datatype_class"];

		if (is_string($datatype_class)) {
			$this -> conf[$datatype]["datatype_class"] = new $datatype_class;
		}
		return $this -> conf[$datatype]["datatype_class"];
	}

	public function which_fits(array $datatypes, string $value): array {
		$fits = [];

		foreach ($datatypes as $datatype) {
			$datatype_key = $this -> uni_datatype_which($datatype);
			
			if (isset($datatype_key) && $this -> conf[$datatype_key]["is"]($value) == true) {
				array_push($fits, [$datatype_key, $datatype]);
			}
		}

		return $fits;
	}

	public function unset_datatype(string $datatype): void {
		$datatype = $this -> getKeyByDataTypeName($datatype) ?? $this -> getKeyByDataTypeName($this -> getDataTypeNameByKey($datatype));
		if (in_array($datatype, array_keys($this -> conf))) {
			unset ($this -> conf[$datatype]);
		}
	}

	public function reset() {
		$this -> __construct();
	}
}