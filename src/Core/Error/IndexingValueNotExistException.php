<?php
namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

/**
 * Class IndexingValueNotExistException
 *
 * Exception class representing an error when a value for indexing does not exist.
 *
 * @package Novaxis\Core\Error
 */
class IndexingValueNotExistException extends Exception {
	/**
	 * The default error message for the indexing value not exist exception.
	 *
	 * @var string
	 */
	protected $message = 'Value for indexing does not exist.';

	/**
	 * IndexingValueNotExistException constructor.
	 *
	 * @param string|null $message The custom error message for the exception (optional).
	 * @param int $line The line number where the exception occurred (optional).
	 */
	public function __construct(?string $message = null, int $line = 0) {
		parent::__construct($message ?? $this -> message, $line);
	}
}
