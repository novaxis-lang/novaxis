<?php
namespace Novaxis\Plugins;

use Novaxis\Core\Runner;
use Novaxis\Core\Executer;

/**
 * Class IntegratedRunner
 *
 * Represents an integrated runner for handling execution and mediation between Runner and Executer instances.
 */
class IntegratedRunner {
	/**
     * @var string|null The filename associated with the runner.
     */
	public ?string $filename;
	
	/**
     * @var Runner The Runner instance.
     */
	public Runner $Runner;

	public string $req_shell_path;
	
	/**
     * IntegratedRunner constructor.
     *
     * @param string|null $filename The optional filename for the runner.
     */
	public function __construct(?string $filename = null, string $req_shell_path) {
		$this -> filename = $filename;
		$this -> req_shell_path = $req_shell_path;
		$this -> Runner = new Runner($filename, null, $this -> req_shell_path);
	}

	/**
     * Executes the runner with optional source input.
     *
     * @param string|null $source The optional source input.
     * @return mixed The result of the runner execution.
     */
	public function runner(?string $source = null) {
		if ($source) {
			$this -> Runner = new Runner(null, $source, $this -> req_shell_path);
		}
		
		return $this -> Runner -> execute();
	}

	/**
     * Mediates between the IntegratedRunner and Runner instances.
     *
     * @return Runner|null The Runner instance or null if not set.
     */
	public function MediateBetweenRunner() {
		if (isset($this -> Runner)) {
			return $this -> Runner;
		}
		return null;
	}

	/**
     * Mediates between the IntegratedRunner and Executer instances through the Runner.
     *
     * @return Executer|null The Executer instance or null if not set.
     */
	public function MediateBetweenExecuter() {
		if (isset($this -> Runner)) {
			return $this -> Runner -> MediateBetweenExecuter();
		}
		return null;
	}
}