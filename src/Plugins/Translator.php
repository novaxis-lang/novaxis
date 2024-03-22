<?php
namespace Novaxis\Plugins;
use Novaxis\Core\Syntax\Token\PathTokens;
use Novaxis\Core\Path;

class Translator {
	use PathTokens;

	/**
     * @var Path The Path instance for managing paths.
     */
	private Path $Path;

	/**
     * Translator constructor.
	 * 
     * Initializes the Path instance.
     */
	public function __construct() {
		$this -> Path = new Path;
	}

	/**
	 * Translate Function
	 *
	 * Translates data from the source format to JSON format.
	 *
	 * @param array $source The source data to be translated.
	 * @return array The translated data in JSON format.
	 */
	public function Translate($source = array()) {
		$result = [];
	
		foreach ($source as $path => $data) {
			$keys = explode(self::PATH_SEPARATOR, $this -> Path -> clean($path));
			$nestedArray = $data;
			if (count(array_keys($nestedArray)) == 3 && in_array('visibility', array_keys($nestedArray)) && in_array('datatype', array_keys($nestedArray)) && in_array('value', array_keys($nestedArray))) {
				$nestedArray = $nestedArray['value'];
			}
	
			while ($key = array_pop($keys)) {
				$nestedArray = [$key => $nestedArray];
			}
	
			$result = array_merge_recursive($result, $nestedArray);
		}
	
		return $result;
	}

	/**
     * OutToFile Function
     *
     * Writes the translated data to a file in JSON format.
     *
     * @param string $filename The name of the file to write to.
     * @param array $source The source data to be written.
     * @return bool True if the write operation is successful, false otherwise.
     */
	public function OutToFile($filename, $source = array()): bool {
		$string = json_encode($source, JSON_PRETTY_PRINT);
		if ($string) {
			if (file_put_contents($filename, $string) !== false) {
				return true;
			}
			return false;
		}
		else {
			return false;
		}
	}
}