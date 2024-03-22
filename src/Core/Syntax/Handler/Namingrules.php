<?php
namespace Novaxis\Core\Syntax\Handler;

use Novaxis\Core\Error\NamingRuleException;

class Namingrules {
	/**
	 * Regular expression pattern for validating naming rules.
	 *
	 * @var string
	 */
	private string $pattern = "/^[a-zA-Z0-9_]*$/";

	/**
	 * Regular expression pattern for fixing the input based on naming rules.
	 *
	 * @var string
	 */
	private string $fix_pattern = "/[^a-zA-Z0-9_]/";

	/**
	 * Check if a given input string is a valid name based on the naming rules.
	 *
	 * @param string $input The input string to validate.
	 * @param bool $throw Whether to throw a NamingRuleException if validation fails.
	 * @return bool True if the input is valid, false otherwise.
	 * @throws NamingRuleException If $throw is true and validation fails.
	 */
	public function isValid(string $input, bool $throw = false): bool {
		$result = preg_match($this -> pattern, $input) && !empty($input);
		
		if ($throw && !$result) {
			throw new NamingRuleException;
		}

		return $result;
	}

	/**
	 * Sanitize the input string based on the naming rules.
	 *
	 * @param string $input The input string to be sanitized.
	 * @return string The sanitized input string.
	 */
	public function fix(string $input) {
		// Remove any characters that are not allowed in the naming rules
		$cleanedInput = preg_replace($this -> fix_pattern, '', $input);
		
		// Ensure the input starts with a letter or underscore
		/* if (!empty($cleanedInput) && !ctype_alpha($cleanedInput[0]) && $cleanedInput[0] !== '_') {
			$cleanedInput = '_' . $cleanedInput;
		} */
		
		return $cleanedInput;
	}
}