<?php
namespace Novaxis\Core;

use Novaxis\Core\Path;
use Novaxis\Core\Executor;
use Novaxis\Core\File\Reader;
use Novaxis\Plugins\Translator;
use Novaxis\Core\Error\Exception;
use Novaxis\Core\Syntax\Handler\CommentHandler;
use Novaxis\Core\Syntax\Handler\Variable\VisibilitySyntax;

/**
 * The Runner class is responsible for executing the Novaxis code stored in a file.
 *
 * This class reads the Novaxis code from a file, processes it line by line, and executes
 * the commands based on the defined syntax. It uses the Executor and Path classes to handle the parsing and execution of the Novaxis code.
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

	/**
	 * Translates Novaxis code to/from another configuration language.
	 *
	 * @var Translator
	 */
	private Translator $Translator;

	/**
	 * An instance of the Executor class for executing Novaxis code.
	 *
	 * @var Executor
	 */
	private Executor $Executor;

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
	public function __construct(?string $filename = null, ?string $source = null, string $req_shell_path) {
		$this -> filename = $filename;
		$this -> source = $source;
		$this -> Reader = new Reader($this -> filename, $this -> source);
		$this -> Translator = new Translator();
		$this -> Executor = new Executor(new Path, $filename, $req_shell_path);
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
		$value = array();

		try {
			foreach ($lines as $lineNumber => $line) {
				if ($lineNumber == count($lines)) {
					$lastline = true;
				}
				$nextLine = next($lines);
				$oldline = $line;

				if ($this -> Executor -> hasUnnecessaryLines($line) === true) {
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
				
				$value = $this -> Executor -> parameter($previousLine, $line, $oldline, $nextLine, $firstline, $lastline, $lineNumber);
				$firstline = false;
		
				$previousLine = $line;
			}
			
			// $value = $this -> Executor -> parameter($previousLine, end($lines), null, $firstline);
			
			if (gettype($value ?? null) === 'NULL') {
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
	 * Mediates between the Runner and Executor instances.
	 *
	 * @return Executor The Executor instance.
	 */
	public function MediateBetweenExecutor() {
		return $this -> Executor;
	}

	/**
	 * Mediates between the Runner and Executor's ElementsLines.
	 *
	 * @return array The ElementsLines from the Executor.
	 */
	public function MediateBetweenElementsLines() {
		return $this -> Executor -> ElementsLines;
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