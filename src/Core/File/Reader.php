<?php
namespace Novaxis\Core\File;

use Novaxis\Core\Error\FileNotFoundException;

/**
 * Class Reader
 *
 * The Reader class is responsible for reading the contents of a file.
 */
class Reader {
	/**
	 * @var ?string The filename of the file to read.
	 */
	private ?string $filename;

	private ?string $source;

	/**
	 * Reader constructor.
	 *
	 * Creates a new Reader object with the given filename.
	 *
	 * @param string $filename The filename of the file to read.
	 */
	function __construct(?string $filename = null, ?string $source = null){
		$this -> filename = $filename;
		$this -> source = $source;
	}

	/**
	 * Read the contents of the file.
	 *
	 * This function reads the contents of the file specified by the filename property and returns the content as a string.
	 *
	 * @return string|false The contents of the file as a string, or false on failure.
	 */
	function read(){
		if ($this -> source) {
			return $this -> source;
		}

		return file_get_contents($this -> filename);
	}

	/**
	 * Read the contents of the specified file and return them as an array of lines with line numbers.
	 *
	 * @return array An associative array where the keys are line numbers (starting from 1) and the values are the lines of the file.
	 * @throws FileNotFoundException If the specified file does not exist or cannot be accessed.
	 */
	public function read_removed(): array {
		if (!file_exists($this -> filename) && empty($this -> source)) {
			throw new FileNotFoundException("The specified '{$this -> filename}' file could not be located or accessed.");
		}
		
		$fileContent = $this -> read();
		$file_lines = [];
	
		$lines = explode(PHP_EOL, $fileContent);
	
		foreach ($lines as $lineNumber => $line) {
			$file_lines[$lineNumber + 1] = $line;
		}
	
		return $file_lines;
	}
}