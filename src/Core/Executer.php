<?php
namespace Novaxis\Core;

use Novaxis\Core\Tabs;
use Novaxis\Core\Syntax\Handler\{
	ClassHandler,
	CommentHandler,
	DatatypeHandler,
	VariableHandler
};
use Novaxis\Core\Syntax\Handler\Variable\{
	Interpolation,
	ListMethod\Counter as listCounter
};
use Novaxis\Core\Error\NotAllowedException;
use Novaxis\Core\Syntax\Datatype\InheritanceType;
use Novaxis\Core\Syntax\Handler\H_Class\MaximumElements;

/**
 * Executes Novaxis code by handling syntax, data types, path, and inheritance for items, and others.
 *
 * @package Novaxis\Core
 */
class Executer {
	/**
	 * @var Tabs The Tabs instance for handling indentation levels.
	 */
	private Tabs $Tabs;

	/**
	 * @var Path The Path instance for managing the path and inheritance of items.
	 */
	private Path $Path;

	/**
	 * @var ClassHandler The ClassHandler instance for handling class-related syntax.
	 */
	private ClassHandler $ClassHandler;
	
	/**
	 * @var CommentHandler The CommentHandler instance for handling comments in the code.
	 */
	private CommentHandler $CommentHandler;

	/**
	 * @var DatatypeHandler The DatatypeHandler instance for managing data types.
	 */
	private DatatypeHandler $DatatypeHandler;

	/**
	 * @var VariableHandler The VariableHandler instance for handling variable syntax.
	 */
	private VariableHandler $VariableHandler;

	/**
	 * @var InheritanceType The InheritanceType instance for managing inheritance.
	 */
	private InheritanceType $InheritanceType;

	private listCounter $listCounter;

	/**
	 * @var Interpolation The Interpolation instance for handling variable interpolation.
	 */
	private Interpolation $Interpolation;
	
	/**
	 * @var MaximumElements The maximum number of elements allowed.
	 */
	private MaximumElements $MaximumElements;

	/**
	 * @var array The master array for list counter started status and name.
	 */
	private array $listCounterStartedMaster = ["started" => false, "name" => null];

	/**
	 * @var array The array for list counter started status and name.
	 */
	private array $listCounterStarted = ["started" => false, "name" => null];

	/**
	 * @var int The number of tabs in the list counter.
	 */
	private int $listCounterLineTabs = 0;

	/**
	 * @var bool Indicates whether the code was in list counter mode.
	 */
	private bool $wasInListCounter = false;

	/**
     * @var array Holds information about code elements.
     */
	public array $ElementsLines = [];

	/**
     * @var string|null The filename associated with the code.
     */
	public ?string $filename;

	/**
	 * Constructor for the Executer class.
	 *
	 * @param Path $Path An instance of the Path class used for managing the path and navigating the nested structure of items.
	 */
	public function __construct(Path $Path, ?string $filename = null) {
		$this -> Tabs = new Tabs;
		$this -> Path = $Path;
		$this -> ClassHandler = new ClassHandler;
		$this -> CommentHandler = new CommentHandler;
		$this -> DatatypeHandler = new DatatypeHandler;
		$this -> VariableHandler = new VariableHandler;
		$this -> InheritanceType = new InheritanceType;
		$this -> listCounter = new listCounter;
		$this -> Interpolation = new Interpolation;
		$this -> MaximumElements = new MaximumElements;
		$this -> filename = $filename;
	}

	/**
	 * Checks if a given line of code contains unnecessary lines or comments.
	 *
	 * @param string|null $line The line of code to check for unnecessary lines.
	 * @return bool Returns true if the line contains unnecessary lines.
	 */
	public function hasUnnecessaryLines(?string $line): bool {
		if ($this -> CommentHandler -> is($line ?? '') or empty(trim($line ?? ''))) {
			if ($this -> CommentHandler -> is($line)) {
				if (empty(trim($this -> CommentHandler -> split($line)))) {
					return true;
				}
			}

			else if (empty(trim($line))) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if adding a new item is allowed based on maximum element limits.
	 *
	 * @param mixed $allVariableDetails All details of the new item to be added.
	 * @throws NotAllowedException If adding a new item is not allowed due to maximum element limits.
	 */
	public function isAddingNewItemAllowed($allVariableDetails, $currentLine, $oldcurrentline, int $lineNumber) {
		if ($this -> MaximumElements -> allowed($this -> Path -> getFullPath())) {
			$this -> Path -> addItem($allVariableDetails['name'], ucfirst($allVariableDetails['datatype']), $allVariableDetails['value'], $allVariableDetails['visibility']);
			$this -> ElementsLines[$this -> Path -> tempForward($allVariableDetails['name'])] = array("Variable", $allVariableDetails, $currentLine, $oldcurrentline, $lineNumber);
			$this -> MaximumElements -> loseAChance($this -> Path -> getFullPath());
		}
		else {
			throw new NotAllowedException;
		}
	}

	/**
	 * Process the current line for the list counter and update related settings.
	 *
	 * @param string $currentLine The current line of code to process.
	 * @return array|null An array of items if the list counter has completed, null otherwise.
	 */
	public function executiveListCounter($currentLine, $oldcurrentline, int $lineNumber) {
		if ($this -> listCounterStarted['started'] == true) {
			$this -> listCounter -> should($currentLine);
			$this -> listCounter -> storage(trim($currentLine));
	
			if ($this -> listCounter -> able()) {
				$value = $this -> listCounter -> getStorage();
				$value = ($this -> Interpolation -> execute($value, $this -> Path -> getItems(), $this -> Path -> clean($this -> Path -> getFullPath()))) ?: $value;
				
				// $this -> Path -> addItem($this -> listCounterStarted['name'], $this -> listCounterStarted['datatype'], $value, $this -> listCounterStarted['visibility']); // Continuous updating
				
				$this -> DatatypeHandler -> createDatatype('list', $value);
				$value = $this -> DatatypeHandler -> getValue();
				
				$this -> listCounterStarted['value'] = $value;

				$this -> isAddingNewItemAllowed($this -> listCounterStarted, $currentLine, $oldcurrentline, $lineNumber);
				
				$this -> wasInListCounter = true;
				$this -> listCounterStarted = $this -> listCounterStartedMaster;

				return $this -> Path -> getItems();
			}
			else {
				return;
			}
		}
	}

	/**
	 * Reset the settings related to the list counter.
	 * 
	 * @return $this
	*/
	public function resetListCounterSettings() {
		$this -> listCounterLineTabs = 0;
		$this -> wasInListCounter = false;

		return $this;
	}

	/**
	 * Process the current line for class-related data and update the Path accordingly.
	 *
	 * @param string $currentLine The current line of code to process.
	 * @param array $values Additional values to assist in processing.
	 * @return array|null An array of items if class handling is detected, null otherwise.
	 */
	public function executiveClass($currentLine, $oldcurrentline, array $values, ?int $lineNumber = 0) {
		$forwardClassName = null;
		$classDatatype = null;

		if ($this -> ClassHandler -> isClass($currentLine)) {
			$tabHandlingExecuteConnection = $this -> Tabs -> execute($this -> ClassHandler, $values["tabHandling"] ?? null, $values["previousLine"] ?? null, $currentLine, $values["nextLine"] ?? null, $values["firstLine"] ?? null, $values["lastLine"] ?? null);
			if ($tabHandlingExecuteConnection === null) {
				return $this -> Path -> getItems();
			}
			$forwardClassName = $tabHandlingExecuteConnection['forwardClassName'];
			$classDatatype = $tabHandlingExecuteConnection['classDatatype'];
			
			if ($forwardClassName || $classDatatype) {
				$this -> Path -> forward(trim($forwardClassName));
				$this -> ElementsLines[$this -> Path -> getFullPath()] = array("Class", $forwardClassName, $currentLine, $oldcurrentline, $lineNumber);
				
				if ($classDatatype) {
					$this -> InheritanceType -> addItem($this -> Path -> getFullPath(), $classDatatype);
				}
			}
			
			if ($this -> ClassHandler -> hasMaximumNumber($currentLine)) {
				$maximumValue = $this -> ClassHandler -> getMaximumNumber($currentLine);
				$maximumValue = ($this -> Interpolation -> execute($maximumValue, $this -> Path -> getItems(), $this -> Path -> clean($this -> Path -> getFullPath()))) ?: $maximumValue;
				
				$this -> MaximumElements -> addItem($this -> Path -> getFullPath(), $maximumValue);
			}
			
			return $this -> Path -> getItems();
		}
	}

	/**
	 * Check if a variable should inherit its datatype based on certain conditions.
	 *
	 * @param array $allVariableDetails Details of the variable to check.
	 * @return mixed The inheritance datatype if applicable, null otherwise.
	 */
	public function isInheritanceDatatype($allVariableDetails) {
		if ($allVariableDetails['datatype'] === null || empty($allVariableDetails['datatype'])) {
			return $this -> InheritanceType -> getItem($this -> Path -> getFullPath());
		}

		return null;
	}

	/**
	 * Start the list counter execution based on given line and variable details.
	 *
	 * @param string $currentLine The current line of code.
	 * @param array $allVariableDetails Details of the variable.
	 * @return bool False if the list counter execution started, true otherwise.
	 */
	public function listCounterStartExecute($currentLine, $oldcurrentline, $allVariableDetails, int $lineNumber) {
		if ($this -> listCounter -> is($allVariableDetails)) {
			$this -> listCounterLineTabs = $this -> Tabs -> getTabCountInLine($currentLine);
			$this -> listCounter -> should($allVariableDetails['value']);

			if (!($this -> listCounter -> able())) {
				$this -> listCounter -> storage($allVariableDetails['value']);
				$this -> listCounterStarted = [
					"started" => true,
					"name" => $allVariableDetails['name'],
					"datatype" => 'List',
					"visibility" => $allVariableDetails['visibility']
				];
				
				return false;
			}

			$this -> listCounterLineTabs = 0;
		}


		return true;
	}

	/**
	 * Execute variable handling based on the given current line.
	 *
	 * @param string $currentLine The current line of code.
	 * @return array|null The updated list of items, or null if no variable was found.
	 */
	public function executiveVariable($currentLine, $oldcurrentline, int $lineNumber) {
		if ($this -> VariableHandler -> isVariable($currentLine)) {
			$allVariableDetails = $this -> VariableHandler -> getAllVariableDetails($currentLine);
			
			$allVariableDetails['value'] = $this -> CommentHandler -> is($allVariableDetails['value']) ? $this -> CommentHandler -> split($allVariableDetails['value']) : $allVariableDetails['value'];
			$allVariableDetails['datatype'] = $this -> isInheritanceDatatype($allVariableDetails) ?: $allVariableDetails['datatype'];
			
			$allVariableDetails['value'] = ($this -> Interpolation -> execute($allVariableDetails['value'], $this -> Path -> getItems(), $this -> Path -> clean($this -> Path -> getFullPath()))) ?: $allVariableDetails['value'];
			$allVariableDetails['datatype'] = $this -> DatatypeHandler -> datatypeInterpolation($allVariableDetails['datatype'], $this -> Path -> getItems(), $this -> Path -> getFullPath());

			if ($this -> listCounterStartExecute($currentLine, $oldcurrentline, $allVariableDetails, $lineNumber) === false) {
				return;
			}

			$this -> DatatypeHandler -> createDatatype($allVariableDetails['datatype'], $allVariableDetails['value']);
			$allVariableDetails['value'] = $this -> DatatypeHandler -> getValue();

			if ($this -> DatatypeHandler -> getDatatype() === 'Auto') {
				$autoValues = $this -> DatatypeHandler -> getDatatypeConnection() -> getItem();
				
				$allVariableDetails['datatype'] = $autoValues['datatype'];
				$allVariableDetails['value'] = $autoValues['value'];
			}
			
			$this -> isAddingNewItemAllowed($allVariableDetails, $currentLine, $oldcurrentline, $lineNumber);
			return $this -> Path -> getItems();
		}
	}
	
	/**
	 * Execute class box handling based on the given current line.
	 *
	 * @param string $currentLine The current line of code.
	 * @return ?array The updated list of items.
	 */
	public function executiveClassbox($currentLine, $oldcurrentline, int $lineNumber) {
		if ($this -> ClassHandler -> isClassBox($currentLine)) {
			$classBox = $this -> ClassHandler -> getClassBox($currentLine);
			$this -> ElementsLines[$this -> Path -> getFullPath() . "-classbox"] = array("Classbox", $classBox, $currentLine, $oldcurrentline, $lineNumber);
			
			$path = $this -> Path /* -> backward() */ -> getFullPath();
			
			if ($this -> InheritanceType -> isUnsetKeyword($classBox)) {
				$this -> InheritanceType -> unset($path);
			}
			else {
				$this -> InheritanceType -> addItem($path, $classBox);
			}

			return $this -> Path -> getItems();
		}
	}

	/**
	 * Execute various parts of code handling based on the given current line.
	 *
	 * @param string $currentLine The current line of code.
	 * @param array|null $values Additional values for specific code parts.
	 * @return array|null An associative array containing the results of executed code parts.
	 */
	public function executiveParts($currentLine, $oldcurrentline, ?array $values = [], int $lineNumber, string $mode) {
		if ($mode == 'listCounter') {
			return $this -> executiveListCounter($currentLine, $oldcurrentline, $lineNumber);
		} elseif ($mode == 'variable') {
			return $this -> executiveVariable($currentLine, $oldcurrentline, $lineNumber);
		} elseif ($mode == 'classbox') {
			return $this -> executiveClassbox($currentLine, $oldcurrentline, $lineNumber);
		} elseif ($mode == 'class') {
			return $this -> executiveClass($currentLine, $oldcurrentline, $values, $lineNumber);
		} else {
			return;
		}

		// $return = [
		// 	"listCounter" => $this -> executiveListCounter($currentLine, $oldcurrentline, $lineNumber),
		// 	"variable" => $this -> executiveVariable($currentLine, $oldcurrentline, $lineNumber),
		// 	"classbox" => $this -> executiveClassbox($currentLine, $oldcurrentline, $lineNumber)
		// ];
		
		// if ($values) {
		// 	$return["class"] = $this -> executiveClass($currentLine, $oldcurrentline, $values, $lineNumber);
		// }

		// return $return;
	}

	/**
	 * The parameter function is responsible for processing and handling the parameter line in Novaxis code.
	 *
	 * @param string|null $previousLine The previous line of code.
	 * @param string|null $currentLine The current line of code.
	 * @param string|null $nextLine The next line of code.
	 * @param bool $firstline A boolean flag indicating if it's the first line of the code.
	 * @return array|null An array containing the items created from the processed lines.
	 */
	public function parameter(?string $previousLine, ?string $currentLine, ?string $oldcurrentline, ?string $nextLine, $firstline = false, $lastline = false, int $lineNumber) {
		$tabHandling = $this -> Tabs -> handling($previousLine, $currentLine);
		$currentLine = $this -> CommentHandler -> is($currentLine) ? $this -> CommentHandler -> split($currentLine) : $currentLine;
		
		if ($this -> listCounterStarted['started'] == true) {
			$return = $this -> executiveParts($currentLine, $oldcurrentline, [], $lineNumber, 'listCounter');
		}
		
		if (($tabHandling == 'backward' || $this -> wasInListCounter === true) && empty($return) && $this -> listCounterStarted['started'] != true) {
			$this -> Path -> backward($this -> Tabs -> getDifferenceNumbers($this -> wasInListCounter === false ? $this -> Tabs -> getTabCountInLine($previousLine) : $this -> listCounterLineTabs, $this -> Tabs -> getTabCountInLine($currentLine)));
			
			if ($this -> wasInListCounter === true) {
				$this -> resetListCounterSettings();
			}
		}
		
		if ($this -> ClassHandler -> isClassBox($currentLine) && empty($return)) {
			$return = $this -> executiveParts($currentLine, $oldcurrentline, [], $lineNumber, 'classbox');
		}
		
		if ($this -> ClassHandler -> isClass($currentLine) && empty($return)) {
			$return = $this -> executiveParts($currentLine, $oldcurrentline, [
				"tabHandling" => $tabHandling,
				"previousLine" => $previousLine,
				"nextLine" => $nextLine,
				"firstLine" => $firstline,
				"lastLine" => $lastline
			], $lineNumber, 'class');
		}
		
		if ($this -> VariableHandler -> isVariable($currentLine) && empty($return)) {
			$return = $this -> executiveParts($currentLine, $oldcurrentline, [], $lineNumber, 'variable');
		}

		return $return ?? null;
	}

	/**
	 * Get Elements Lines
	 *
	 * Retrieves information about code elements.
	 *
	 * @return array The array containing information about code elements.
	 */
	public function getElementsLines() {
		return $this -> ElementsLines;
	}
}