<?php
namespace Novaxis\Core\Syntax\Token;

/**
 * Trait ImportTokens
 *
 * This trait contains a set of constants related to import tokens.
 */
trait ImportTokens {
	/**
	 * Represents the keyword used for importing classes.
	 */
	const IMPORT_KEYWORD = 'import';
	
	/**
	 * Represents the keyword used for providing aliases to imported classes.
	 */
	const AS_KEYWORD = 'as';
	
	/**
	 * Represents the default alias used when no specific alias is provided.
	 */
	const DEFAULT_ALIAS = '?';
	
	/**
	 * Represents the keyword used for publicly importing classes.
	 */
	const IMPORTING_PUBLICLY_KEYWORD = 'publicly';
	
	/**
	 * Represents the keyword used for privately importing classes.
	 */
	const IMPORTING_PRIVATELY_KEYWORD = 'privately';
}
