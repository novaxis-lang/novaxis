<?php
namespace Novaxis\Core\Syntax\Handler\Variable;

/**
 * Class EscapeSequences
 *
 * This class handles escape sequences for variable values in the input string.
 */
class EscapeSequences {
	/**
	 * Mapping of escaped characters to their corresponding unescaped characters.
	 *
	 * @var array
	 */
	private array $backslashes = [
		"\\\\" => "\\",
		"\#" => "#",
		"\\\"" => "\"",
		"\\/" => "/",
		"\\{" => "{",
		"\S" => " "
	];

	/**
	 * Replaces escape sequences in the input string with their corresponding characters.
	 *
	 * @param string $input The input string containing escape sequences.
	 * @return string The input string with escape sequences replaced.
	 */
	public function replaceEscapeSequences($input) {
		return is_string($input) ? preg_replace_callback('/\\\\\\\\|\\\\[\/#\S]/', function ($match) {
			return isset($this -> backslashes[$match[0]]) ? $this -> backslashes[$match[0]] : $match[0];
		}, $input) : $input;
	}
}