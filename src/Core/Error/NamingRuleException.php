<?php
namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

/**
 * Class NamingRuleException
 *
 * Exception class for handling errors related to naming rule violations.
 *
 * @package Novaxis\Core\Error
 */
class NamingRuleException extends Exception {
	/**
	 * The default error message for the exception.
	 *
	 * @var string
	 */
	protected $message = 'The provided name violates the naming rules. Make sure the name follows the allowed format.';

	/**
	 * NamingRuleException constructor.
	 *
	 * @param string|null $message The custom error message for the exception (optional).
	 * @param int $line The line number where the exception occurred.
	 */
	public function __construct(?string $message = null, int $line = 0) {
		parent::__construct($message ?? $this -> message, $line);
	}
}