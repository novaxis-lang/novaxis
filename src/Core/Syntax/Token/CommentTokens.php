<?php
namespace Novaxis\Core\Syntax\Token;

/**
 * Trait CommentTokens
 *
 * This trait contains constants for comment tokens used in the syntax.
 */
trait CommentTokens {
	/**
	 * The single-line comment tokens.
	 */
	const COMMENT_DECLARE = ['#', '//'];

	/**
	 * The multi-line comment open token.
	 */
	const MULTI_LINE_COMMENT_OPEN = ['/*'];

	/**
	 * The multi-line comment close token.
	 */
	const MULTI_LINE_COMMENT_CLOSE = ['*/'];
}