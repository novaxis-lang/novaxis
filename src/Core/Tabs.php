<?php
namespace Novaxis\Core;

use Novaxis\Core\Path;
use Novaxis\Core\Syntax\Handler\ClassHandler;

/**
 * The Tabs class handles tab-related operations and tracks indentation levels in code.
 */
class Tabs {
	/**
	 * The previous tab level.
	 *
	 * @var int
	 */
	private int $previousTab = 0;

	/**
	 * The current tab level.
	 *
	 * @var int
	 */
	private int $currentTab = 0;

	/**
	 * An instance of the Path class to handle and manipulate hierarchical paths.
	 *
	 * @var Path
	 */
	private Path $Path;

	/**
	 * Constructor for the Tabs class.
	 */
	public function __construct() {
		$this -> Path = new Path;
	}
	
	/**
     * Get the count of tabs at the beginning of a line.
     *
     * @param string $line The input line to check for tabs.
     * @return int The number of tabs at the beginning of the line.
     */
	function getTabCountInLine($line) {
		$pattern = '/^(\t*|\s*)\S/';
	
		if (preg_match($pattern, $line, $matches)) {
			$tabs = $matches[1];
			return strlen($tabs);
		}
	
		return 0;
	}

	/**
     * Get the difference in tab count between two lines.
     *
     * This function calculates the difference in the number of tabs between two input lines.
     *
     * @param string $previousLine The previous line.
     * @param string $currentLine The current line.
     * @return int The difference in the number of tabs between the two lines.
     */
	public function getDifferenceNumber(string $previousLine, string $currentLine) {
		return $this -> getTabCountInLine($previousLine) - $this -> getTabCountInLine($currentLine);
	}

	/**
	 * Get the difference in tab count between two lines.
	 *
	 * This function calculates the difference in the number of tabs between two input lines.
	 *
	 * @param string $previousLine The previous line.
	 * @param string $currentLine The current line.
	 * @return int The difference in the number of tabs between the two lines.
	 */
	public function getDifferenceNumbers(int $previousLine, int $currentLine) {
		return $previousLine - $currentLine;
	}

	/**
	 * Determine how to handle tab indentation between two lines.
	 *
	 * This function takes two input lines and determines how to handle tab indentation between them.
	 *
	 * @param string|null $previousLine The previous line.
	 * @param string|null $currentLine The current line.
	 * @return string|null The handling type, which can be 'forward', 'backward', or 'nothing'.
	 */
	public function handling(?string $previousLine, ?string $currentLine): ?string {
		$previousLineTabs = $this -> getTabCountInLine($previousLine);
		$currentLineTabs = $this -> getTabCountInLine($currentLine);

		if ($previousLineTabs < $currentLineTabs) {
			return 'forward';
		}
		else if ($previousLineTabs > $currentLineTabs) {
			return 'backward';
		}
		else if ($previousLineTabs == $currentLineTabs) {
			return 'nothing';
		}
	}

	/**
	 * Execute the tab handling logic and retrieve class information.
	 *
	 * This function executes the tab handling logic based on input parameters and returns information about the class.
	 *
	 * @param ClassHandler $classHandler The ClassHandler instance for handling class-related syntax.
	 * @param mixed $tabHandling The tab handling mode.
	 * @param string $previousLine The previous line.
	 * @param string $currentLine The current line.
	 * @param string $nextLine The next line.
	 * @param bool $firstline Indicates if it's the first line.
	 * @return array|void An array containing class information:
	 *   - 'forwardClassName': The class name in forward tab handling.
	 *   - 'classDatatype': The class datatype.
	 */
	public function execute(ClassHandler $classHandler, $tabHandling, $previousLine, $currentLine, $nextLine, $firstline, $lastline) {
		if (($tabHandling == 'forward' || $firstline) && $this -> handling($currentLine, $nextLine) == 'forward') {
			$forwardClassName = $classHandler -> getClassName($currentLine);
			$classDatatype = $classHandler -> getClassDatatype($currentLine);
		}
		else if ($tabHandling == 'backward') {
			if ($this -> handling($currentLine, $nextLine) == 'forward') {
				$forwardClassName = $classHandler -> getClassName($currentLine);
				$classDatatype = $classHandler -> getClassDatatype($currentLine);
			}
		}
		else if ($tabHandling == 'nothing' && $this -> handling($currentLine, $nextLine) == 'forward') {
			$forwardClassName = $classHandler -> getClassName($currentLine);
			$classDatatype = $classHandler -> getClassDatatype($currentLine);
		}
		else if ($lastline) {
			return;
		}

		return [
			'forwardClassName' => $forwardClassName ?? null,
			'classDatatype' => $classDatatype ?? null
		];
	}
}