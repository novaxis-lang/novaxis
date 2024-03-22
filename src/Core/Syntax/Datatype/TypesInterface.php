<?php
namespace Novaxis\Core\Syntax\Datatype;

/**
 * Interface TypesInterface
 * Represents the interface for datatype classes in Novaxis.
 *
 * @package Novaxis\Core\Syntax\Datatype
 */
interface TypesInterface {
	/**
	* Sets the input value for the datatype instance.
	*
	* @param mixed $input The input value.
	*/
	public function setValue($input);

	/**
	* Gets the value of the datatype instance.
	*
	* @return mixed The value of the datatype instance.
	*/
	public function getValue();

	/**
	* Converts the current value to a proper datatype representation.
	*/
	public function convertTo();
}