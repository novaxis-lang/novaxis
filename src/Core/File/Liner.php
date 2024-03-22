<?php
namespace Novaxis\Core\File;

class Liner {
	/**
     * @var string The filename to operate on.
     */
	private string $filename;

	/**
     * Liner constructor.
     *
     * @param string $filename The filename to operate on.
     */
	public function __construct(string $filename) {
		$this -> filename = $filename;
	}

	/**
     * Change a specific line in the file.
     *
     * @param int $lineNumber The line number to change.
     * @param mixed $newContent The new content to replace the line.
     *
     * @return bool True if the change is successful, false otherwise.
     */
	public function changeLine($lineNumber, $newContent) {
		$lines = file($this -> filename);
		$lineNumber --;
	
		if ($lineNumber >= 0 && $lineNumber <= count($lines)) {
			$lines[$lineNumber] = rtrim($newContent) . PHP_EOL;
		} else {
			return false;
		}
	
		file_put_contents($this -> filename, implode('', $lines));
	
		return true;
	}
}