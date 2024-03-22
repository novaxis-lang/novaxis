<?php
namespace Novaxis\Core\Syntax\Token;

/**
 * Trait VariableTokens
 *
 * This trait contains a set of constants related to variable tokens.
 */
trait VariableTokens {
	/**
	 * The list of valid symbols used for declaring variable values in assignments.
	 */
	const VALUE_DECLARE = ['=', ':'];

	/**
	 * The list of valid symbols used for declaring datatypes in variable declarations.
	 */
	const DATATYPE_DECLARE = ['?'];

	/**
	 * The interpolation open token.
	 */
	const INTERPOLATION_OPEN = ['{'];

	/**
	 * The interpolation open token.
	 */
	const INTERPOLATION_CLOSE = ['}'];

	/**
	 * Visibility keywords and their display values.
	 */
	const VISIBILITY_KEYWORDS = [
		"public" => "Public",
		"protected" => "Protected",
		"inherited" => "Inherited",
		"private" => "Private",
		"restricted" => "Restricted",
	];
}