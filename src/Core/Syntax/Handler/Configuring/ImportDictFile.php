<?php
namespace Novaxis\Core\Syntax\Handler\Configuring;

use Novaxis\Core\Path;
use Novaxis\Core\File\Reader;
use Novaxis\Core\Syntax\Token\PathTokens;

class ImportDictFile {
	use PathTokens;
	
	private Path $Path;
	private Reader $Reader;
	public string $filename;
	
	public function __construct(string $filename) {
		$this -> filename = $filename;
		$this -> Path = new Path;
		$this -> Reader = new Reader($this -> filename);
	}

	function flattenArray($array, $visibility, $prefix = '') {
		$result = [];
		foreach ($array ?? [] as $key => $value) {
			if (is_array($value)) {
				$flattened = $this -> flattenArray($value, $visibility, $prefix . $key . '.');
				$result = array_merge($result, $flattened);
			} else {
				// There is not datatype, for now
				// "datatype" => gettype($value)
				$result[$prefix . $key] = array("visibility" => (string) $visibility, "datatype" => "auto", "value" => $value);
			}
		}
		return $result;
	}

	public function handle($visibility, $path = null) {
		$file_content = $this -> Reader -> read();
		$dict_data = json_decode($file_content, true);
		$array = $this -> flattenArray($dict_data, $visibility, ($path && is_string($path)) ? $this -> Path -> clean($path) . self::PATH_SEPARATOR : '');
		return $array;
	}
}