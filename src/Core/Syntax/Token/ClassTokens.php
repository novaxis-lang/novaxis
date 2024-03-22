<?php
namespace Novaxis\Core\Syntax\Token;

/**
 * Trait ClassTokens
 *
 * This trait contains a set of constants related to class tokens.
 */
trait ClassTokens {
	/**
	 * The list of valid datatype declaration symbols used in class properties.
	 */
	const DATATYPE_DECLARE = ['?'];

	/**
	 * Represents the 'Unset' keyword used for unsetting the current classbox.
	 */
	const UNSET_CLASSBOX_KEYWORD = 'unset';

	/**
	 * Represents the option for case-insensitive unsetting of the classbox.
	 *
	 * When set to true, it allows unsetting the classbox in both uppercase and lowercase forms.
	 * When set to false, it only allows unsetting the classbox in the exact declared string form.
	 */
	const ANYCASE_UNSET_CLASSBOX_KEYWORD = true;
}