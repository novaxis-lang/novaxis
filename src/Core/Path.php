<?php
namespace Novaxis\Core;

use Novaxis\Core\Syntax\Token\PathTokens;
use Novaxis\Core\Syntax\Handler\VariableHandler;
use Novaxis\Core\Syntax\Handler\Variable\EscapeSequences;
// use Novaxis\Core\Error\InvalidVariableVisibilitySyntaxException;

/**
 * The Path class represents a data structure to handle and manipulate hierarchical paths.
 *
 * @package Novaxis\Core
 */
class Path {
	use PathTokens;

	/**
	 * The current path represented as a string.
	 *
	 * @var string
	 */
	private string $path = '';

	/**
	 * Array to store items with paths and associated data.
	 *
	 * @var array
	 */
	private array $items = [];

	/**
	 * Instance of VariableHandler for managing variable's visibility in Novaxis code.
	 *
	 * @var VariableHandler
	 */
	private VariableHandler $VariableHandler;

	/**
	 * The EscapeSequences object for handling escape sequences in variable values.
	 * 
	 * @var EscapeSequences
	 */
	private EscapeSequences $EscapeSequences;

	/**
	 * Constructor for the Path class.
	 */
	public function __construct() {
		$this -> VariableHandler = new VariableHandler;
		$this -> EscapeSequences = new EscapeSequences;
	}

	/**
	 * Resets the current path to an empty string.
	 *
	 * @return $this
	 */
	public function reset() {
		$this -> path = '';
		
		return $this;
	}
	
	/**
	 * Sets the full path to the given input.
	 *
	 * @param string $input The input path to set.
	 * @return $this;
	 */
	public function setFullPath($input) {
		$this -> path = $this -> clean($input);

		return $this;
	}

	/**
	 * Gets the full path.
	 *
	 * @return string The full path.
	 */
	public function getFullPath() {
		return $this -> path;
	}

	/**
	 * Appends a forward path segment to the current path.
	 *
	 * @param string $name The name of the forward segment to append.
	 * @return Path Returns the current Path instance for method chaining.
	 */
	public function forward(string $name) {
		$this -> path .= self::PATH_SEPARATOR . $name;
		$this -> path = $this -> clean($this -> path);

		return $this;
	}

	/**
	 * Gets the full path string after temporarily appending a forward segment.
	 *
	 * @param string $name The name of the forward segment to temporarily append.
	 * @return string The full path string after appending the forward segment.
	 */
	public function tempForward(string $name) {
		return $this -> clean($this -> path . self::PATH_SEPARATOR . $name);
	}

	/**
	 * Moves the current path backwards by a specified number of segments.
	 *
	 * @param int $count The number of segments to move backwards.
	 * @return $this
	 */
	public function backward(int $count = 1) {
		if (!str_contains($this -> path, self::PATH_SEPARATOR)) {
			// Moved
		}
		$this -> path = $this -> clean($this -> path);
		$this -> path = self::PATH_SEPARATOR . $this -> path;
		
		$pathSegments = explode(self::PATH_SEPARATOR, $this -> path);
		
		$numSegments = count($pathSegments);
		$numSteps = min($count, $numSegments - 1);
		
		for ($i = 0; $i < $numSteps; $i++) {
			array_pop($pathSegments);
		}
		
		$this -> path = $this -> clean(implode(self::PATH_SEPARATOR, $pathSegments));
		
		return $this;
	}

	/**
	 * Gets an array containing all individual segments of the path.
	 *
	 * @return array An array containing all individual segments of the path.
	 */
	public function getSegments() {
		// Return an array containing all individual segments of the path.
		return explode(self::PATH_SEPARATOR, $this -> path);
	}
	
	/**
	 * Gets the parent path by removing the last segment.
	 *
	 * @return string The parent path.
	 */
	public function getParent() {
		$pathSegments = explode(self::PATH_SEPARATOR, $this -> path);
		
		array_pop($pathSegments);

		return $this -> clean(implode(self::PATH_SEPARATOR, $pathSegments));
	}

	/**
	 * Cleans the path by removing duplicate path separators and trimming leading and trailing separators.
	 *
	 * @param string $path The input path to clean.
	 * @return string The cleaned path.
	 */
	public function clean($path) {
		$path = preg_replace('#\\'.self::PATH_SEPARATOR.'{2,}#', self::PATH_SEPARATOR, $path); // Regex for PATH_SEPARATOR
		$path = trim($path, self::PATH_SEPARATOR);

		return $path;
	}

	/**
	 * Adds an item with a path and associated data to the collection.
	 *
	 * @param string $name The name of the item.
	 * @param string $datatype The datatype associated with the item.
	 * @param mixed $value The value associated with the item.
	 * @param string $visibility The visibility of the item.
	 * @return $this
	 */
	public function addItem(string $name, string $datatype, mixed $value, string $visibility) {
		$path = $this -> tempForward($name);

		$this -> items[$this -> clean($path)] = array (
			'visibility' => $visibility,
			'datatype' => $datatype,
			'value' => $this -> EscapeSequences -> replaceEscapeSequences($value),
		);

		return $this;
	}

	/**
	 * Gets all items with path and associated data mappings.
	 *
	 * @return array The items with path and associated data mappings.
	 */
	public function getItems() {
		return $this -> items;
	}
}