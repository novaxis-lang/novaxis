<?php
namespace Novaxis\Plugins\Controller;
use Novaxis\Core\Path;
use Novaxis\Core\Executer;
use Novaxis\Core\File\Liner;
use Novaxis\Core\Syntax\Handler\ClassHandler;
use Novaxis\Core\Error\SetPathNotFoundException;
use Novaxis\Core\Syntax\Handler\VariableHandler;

class Mediator {
	/**
     * @var Path The Path instance for managing paths.
     */
	private Path $Path;

	/**
     * @var Executer The Executer instance for handling execution.
     */
	private Executer $Executer;

	/**
     * @var Liner The Liner instance for working with file lines.
     */
	private Liner $Liner;

	/**
     * @var ClassHandler The ClassHandler instance for handling classes.
     */
	private ClassHandler $ClassHandler;

	/**
     * @var VariableHandler The VariableHandler instance for handling variables.
     */
	private VariableHandler $VariableHandler;

	/**
     * @var string|null The path associated with the mediator.
     */
	private ?string $path;

	/**
     * @var array The elements lines associated with the mediator.
     */
	private array $ElementsLines;

	/**
     * @var string The filename associated with the mediator.
     */
	private string $filename;

	/**
     * Mediator constructor.
	 * 
     * Initializes the Path, Executer, Liner, ClassHandler, and VariableHandler instances.
     */
	public function __construct(Executer $Executer) {
		$filename = $Executer -> filename;
		$this -> Path = new Path;
		$this -> Executer = $Executer;
		$this -> Liner = new Liner($filename);
		$this -> ClassHandler = new ClassHandler;
		$this -> VariableHandler = new VariableHandler;
		$this -> filename = $filename;
	}

	/**
	 * Set the path for the Mediator.
	 *
	 * @param string $path The path to set.
	 * @param bool $classBox Whether the path corresponds to a class box.
	 * @return $this Returns the current Mediator instance.
	 * @throws SetPathNotFoundException If the specified path or class box is invalid or does not exist.
	 */
	public function set(string $path, $classBox = false) {
		$this -> path = $this -> Path -> clean($path . ($classBox ? '-classbox' : ''));
		if (!($this -> isExists())) {
			throw new SetPathNotFoundException(
				$classBox ?
					"By Mediator: The entered classbox \"$path\" is invalid or does not exist." :
					"By Mediator: The entered path \"$path\" is invalid or does not exist."
			);
		}
		return $this;
	}

	/**
	 * Unset the current path in the Mediator.
	 *
	 * @return $this Returns the current Mediator instance.
	 */
	public function unset() {
		$this -> path = null;
		return $this;
	}

	/**
	 * Check if the current path in the Mediator exists.
	 *
	 * @return bool Returns true if the path exists, false otherwise.
	 */
	public function isExists() {
		return in_array($this -> path, array_keys($this -> Executer -> ElementsLines));
	}

	/**
	 * Check if the Mediator has a set path.
	 *
	 * @return bool Returns true if a path is set, false otherwise.
	 */
	public function isset() {
		return !empty($this -> path);
	}

	/**
	 * Check if the current path in the Mediator corresponds to a class.
	 *
	 * @return bool Returns true if the path represents a class, false otherwise.
	 */
	public function isClass() {
		return $this -> Executer -> ElementsLines[$this -> Path -> clean($this -> path)][0] == "Class" ? true : false;
	}

	/**
	 * Check if the current path in the Mediator corresponds to a variable.
	 *
	 * @return bool Returns true if the path represents a variable, false otherwise.
	 */
	public function isVariable() {
		return $this -> Executer -> ElementsLines[$this -> Path -> clean($this -> path)][0] == "Variable" ? true : false;
	}
	
	/**
	 * Check if the current path in the Mediator corresponds to a classbox.
	 *
	 * @return bool Returns true if the path represents a classbox, false otherwise.
	 */
	public function isClassbox() {
		return $this -> Executer -> ElementsLines[$this -> Path -> clean($this -> path)][0] == "Classbox" ? true : false;
	}

	/**
	 * Change the name of the element represented by the current path.
	 *
	 * @param string $name The new name to set.
	 * @param bool $execute If true, execute the change in the code; if false, return the modified line without executing.
	 *
	 * @return string|null Returns the modified line if $execute is false, or null if the operation is not possible or fails.
	 */
	public function nameTo(string $name, bool $execute = false) {
		if ($this -> isset()) {
			$currentLine = $this -> Executer -> ElementsLines[$this -> path][2];

			if ($this -> isClass()) {
				$newLine = $this -> ClassHandler -> changeClassName($currentLine, $name);
			} elseif ($this -> isVariable()) {
				$newLine = $this -> VariableHandler -> changeVariableName($currentLine, $name);
			}

			return $this -> executeChange($execute, $newLine);
		}

		return null;
	}

	/**
	 * Change the datatype of the element represented by the current path.
	 *
	 * @param string $datatype The new datatype to set.
	 * @param bool $execute If true, execute the change in the code; if false, return the modified line without executing.
	 *
	 * @return string|null Returns the modified line if $execute is false, or null if the operation is not possible or fails.
	 */
	public function datatypeTo(string $datatype, bool $execute = false) {
		if ($this -> isset()) {
			$currentLine = $this -> Executer -> ElementsLines[$this -> path][2];

			if ($this -> isClass()) {
				$newLine = $this -> ClassHandler -> changeClassDatatype($currentLine, $datatype);
			} elseif ($this -> isVariable()) {
				$newLine = $this -> VariableHandler -> changeVariableDatatype($currentLine, $datatype);
			} elseif ($this -> isClassbox()) {
				$newLine = $this -> ClassHandler -> changeClassboxDatatype($currentLine, $datatype);
			}

			$this -> executeChange($execute, $newLine);
		}

		return null;
	}

	/**
	 * Change the value of the variable represented by the current path.
	 *
	 * @param string $value The new value to set.
	 * @param bool $execute If true, execute the change in the code; if false, return the modified line without executing.
	 *
	 * @return string|null Returns the modified line if $execute is false, or null if the operation is not possible or fails.
	 */
	public function valueTo(string $value, bool $execute = false) {
		if ($this -> isset()) {
			$currentLine = $this -> Executer -> ElementsLines[$this -> path][3];

			if ($this -> isVariable()) {
				$newLine = $this -> VariableHandler -> changeVariableValue($currentLine, $value);
			}

			return $this -> executeChange($execute, $newLine);
		}

		return null;
	}

	/**
	 * Execute or return a line change depending on the $execute parameter.
	 *
	 * @param bool $execute If true, execute the change in the code; if false, return the modified line without executing.
	 * @param string $newLine The modified line.
	 *
	 * @return string|null Returns the modified line if $execute is false, or null if $execute is true and the operation fails.
	 */
	private function executeChange(bool $execute, string $newLine) {
		if ($execute) {
			return $this -> Liner -> changeLine($this -> Executer -> ElementsLines[$this -> path][4], $newLine);
		} elseif (!empty($newLine)) {
			return $newLine;
		}

		return null;
	}
}