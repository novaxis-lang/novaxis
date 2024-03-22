<?php
namespace Novaxis\Core;

use Novaxis\Core\Path;
use Novaxis\Core\Executer;
use Novaxis\Core\File\Reader;
use Novaxis\Plugins\Translator;
use Novaxis\Core\Error\Exception;
use Novaxis\Core\Syntax\Handler\CommentHandler;
use Novaxis\Core\Syntax\Handler\Variable\VisibilitySyntax;

/**
 * The Runner class is responsible for executing the Novaxis code stored in a file.
 *
 * This class reads the Novaxis code from a file, processes it line by line, and executes
 * the commands based on the defined syntax. It uses the Executer and Path classes to handle the parsing and execution of the Novaxis code.
 */
class Runner {
	/**
	 * The path to the Novaxis file to be executed.
	 *
	 * @var ?string
	 */
	private ?string $filename;

	/**
	 * @var string|null The source data used in the runner.
	 */
	private ?string $source;

	/**
	 * An instance of the Reader class for reading the Novaxis file.
	 *
	 * @var Reader
	 */
	private Reader $Reader;

	private Translator $Translator;

	/**
	 * An instance of the Executer class for executing Novaxis code.
	 *
	 * @var Executer
	 */
	private Executer $Executer;

	/**
	 * An instance of the VisibilitySyntax class to handle variable's visibility.
	 *
	 * @var VisibilitySyntax
	 */
	private VisibilitySyntax $VisibilitySyntax;

	/**
	 * @var CommentHandler The CommentHandler instance for handling comments.
	 */
	private CommentHandler $CommentHandler;

	/**
	 * Runner constructor.
	 *
	 * @param string $filename The path to the Novaxis file to be executed.
	 */
	public function __construct(?string $filename = null, ?string $source = null) {
		$this -> filename = $filename;
		$this -> source = $source;
		$this -> Reader = new Reader($this -> filename, $this -> source);
		$this -> Translator = new Translator();
		$this -> Executer = new Executer(new Path, $filename);
		$this -> CommentHandler = new CommentHandler;
		$this -> VisibilitySyntax = new VisibilitySyntax;
	}

	/**
	 * Get the indentation level of a line in the Novaxis code.
	 *
	 * @param string $line The line of Novaxis code.
	 * @return int The number of leading tabs in the line.
	 */
	function getIndentationLevel($line) {
		return strspn($line, "\t");
	}
	
	/**
	 * Execute the code.
	 *
	 * @return mixed An array containing the items created from the processed lines, or null if an error occurred.
	 * @throws Exception When an eyxception occurs during code execution.
	 */
	public function execute(?bool $translator = false) {
		$lines = $this -> Reader -> read_removed();
		
		$firstline = true;
		$lastline = false;
		$previousLine = null;
		
		try {
			foreach ($lines as $lineNumber => $line) {
				if ($lineNumber == count($lines)) {
					$lastline = true;
				}
				$nextLine = next($lines);
				$oldline = $line;

				if ($this -> Executer -> hasUnnecessaryLines($line) === true) {
					continue;
				}

				if ($this -> CommentHandler -> isOneMultiLine($line)) {
					$line = $this -> CommentHandler -> removeOneMultiLine($line);
					if (empty(trim($line))) {
						continue;
					}
				}
				
				if ($this -> CommentHandler -> isMultiLineCommentClose($line)) {
					$this -> CommentHandler -> multilineEnable = false;
					continue;
				}
		
				if ($this -> CommentHandler -> multilineEnable == true) {
					continue;
				}
		
				if ($this -> CommentHandler -> isMultiLineCommentOpen($line)) {
					$this -> CommentHandler -> multilineEnable = true;
					$line = $this -> CommentHandler -> removeMultiLine($line);
					if (empty(trim($line))) {
						continue;
					}
				}
				
				$value = $this -> Executer -> parameter($previousLine, $line, $oldline, $nextLine, $firstline, $lastline, $lineNumber);
				$firstline = false;
		
				$previousLine = $line;
			}
			
			// $value = $this -> Executer -> parameter($previousLine, end($lines), null, $firstline);	
			
			if (gettype($value) === 'NULL') {
				throw new Exception(null, 0);
			}

			$value = $this -> VisibilitySyntax -> remover($value);
			$json_code = $this -> Translator -> Translate($value);
			if ($translator == null) {
				return [$value, $json_code];
			}
			return $translator == false ? $value : $json_code;
		}
		
		catch (Exception $e){
			$e -> setLineNumber($lineNumber);
			echo $e . PHP_EOL;
		}
		
		catch (\TypeError $e) {
			throw new Exception(null, $lineNumber ?? 0);
		}
	}

	/**
	 * Mediates between the Runner and Executer instances.
	 *
	 * @return Executer The Executer instance.
	 */
	public function MediateBetweenExecuter() {
		return $this -> Executer;
	}

	/**
	 * Mediates between the Runner and Executer's ElementsLines.
	 *
	 * @return array The ElementsLines from the Executer.
	 */
	public function MediateBetweenElementsLines() {
		return $this -> Executer -> ElementsLines;
	}

	/**
	 * Gets the filename associated with the Runner.
	 *
	 * @return string|null The filename.
	 */
	public function getFilename() {
		return $this -> filename;
	}

	/**
	 * Gets the source from the Runner.
	 *
	 * @return string|null The source.
	 */
	public function getSource() {}
}