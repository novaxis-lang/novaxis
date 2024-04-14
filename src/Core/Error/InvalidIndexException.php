<?php
namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

/**
 * Class InvalidIndexException
 *
 * Exception class representing an error when an index is invalid.
 *
 * @package Novaxis\Core\Error
 */
class InvalidIndexException extends Exception {
    /**
     * The default error message for the invalid index exception.
     *
     * @var string
     */
    protected $message = 'Invalid index provided.';

    /**
     * InvalidIndexException constructor.
     *
     * @param string|null $message The custom error message for the exception (optional).
     * @param int $line The line number where the exception occurred (optional).
     */
    public function __construct(?string $message = null, int $line = 0) {
        parent::__construct($message ?? $this -> message, $line);
    }
}
