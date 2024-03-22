<?php
namespace Novaxis\Core\Syntax\Handler;

use Novaxis\Core\Syntax\Token\CommentTokens;

class CommentHandler {
	use CommentTokens;

	/**
	 * The regex pattern used for comment detection.
	 * 
	 * @var string
	 */
	private $pattern;

	/**
     * @var bool $multilineEnable Indicates whether multiline comments are enabled.
     */
	public bool $multilineEnable = false;

	/**
	 * CommentHandler constructor.
	 * 
	 * Initializes the CommentHandler with the proper regex pattern for comment detection.
	 */
	public function __construct() {
		$escapedCharacters = array_map(function($char) {
			return ($char === "//") ? preg_quote($char, '/') : $char;
		}, self::COMMENT_DECLARE);
		$regex = implode('|', $escapedCharacters);

		$this -> pattern = "(?<!\\\\)\s*(?:$regex)";
	}

	/**
	 * Checks if the given line contains a comment.
	 *
	 * @param string $line The input line to check.
	 * @return bool Returns true if the line contains a comment, otherwise false.
	 */
	public function is(string $line): bool {
		return preg_match('/' . $this -> pattern . '/', $line);
	}

	/**
	 * Splits the given line and returns the part before the comment if it exists.
	 *
	 * @param string $line The input line to split.
	 * @return string The part of the line before the comment or the whole line if no comment is found.
	 */
	public function split(string $line) {
		$pattern = '/^(.*?)' . $this -> pattern . '/';

		preg_match($pattern, $line, $matches);

		return isset($matches[1]) ? $matches[1] : $line;
	}

	/**
     * Check if a line contains the opening of a multiline comment.
     *
     * @param string $line The line to check.
     * @return bool True if the line contains the opening of a multiline comment, false otherwise.
     */
	public function isMultiLineCommentOpen(string $line): bool {
		$escapedCharacters = array_map(function($char) {
			return preg_quote($char, '/');
		}, self::MULTI_LINE_COMMENT_OPEN);
		$regex = implode('|', $escapedCharacters);

		return preg_match("/(?<!\\\\)\s*(?:$regex)/", trim($line));
	}

	/**
     * Check if a line contains the closing of a multiline comment.
     *
     * @param string $line The line to check.
     * @return bool True if the line contains the closing of a multiline comment, false otherwise.
     */
	public function isMultiLineCommentClose(string $line): bool {
		$escapedCharacters = array_map(function($char) {
			return preg_quote($char, '/');
		}, self::MULTI_LINE_COMMENT_CLOSE);
		$regex = implode('|', $escapedCharacters);

		return preg_match("/(?<!\\\\)\s*(?:$regex)/", trim($line));
	}

	/**
     * Check if a line is a one-line multiline comment (both open and close in the same line).
     *
     * @param string $line The line to check.
     * @return bool True if the line is a one-line multiline comment, false otherwise.
     */
	public function isOneMultiLine(string $line): bool {
		return $this -> isMultiLineCommentOpen($line) && $this -> isMultiLineCommentClose(trim($line));
	}

	/**
     * Remove a one-line multiline comment from a line.
     *
     * @param string $line The line to remove the comment from.
     * @return string The line without the one-line multiline comment.
     */
	public function removeOneMultiLine(string $line) {
		return preg_replace("/(?<!\\\\)(?:\/\*)[\s\S]*?(?<!\\\\)\s*(?:\*\/)/", "", $line);
	}
	
	/**
     * Remove a multiline comment from a line.
     *
     * @param string $line The line to remove the comment from.
     * @return string The line without the multiline comment.
     */
	public function removeMultiLine(string $line) {
		return preg_replace("/(?<!\\\\)(?:\/\*)(\w|\W){0,}/", "", $line);
	}
}