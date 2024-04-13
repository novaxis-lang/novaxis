<?php
namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

/**
 * Class InvalidIndexingTargetException
 *
 * Exception class representing an error when attempting to index on a non-array or non-iterable type.
 *
 * @package Novaxis\Core\Error
 */
class InvalidIndexingTargetException extends Exception {
    /**
     * The default error message for the invalid indexing target exception.
     *
     * @var string
     */
    protected $message = "Indexing on non-array or non-iterable type is not allowed.";

    /**
     * InvalidIndexingTargetException constructor.
     *
     * @param string|null $message The custom error message for the exception (optional).
     * @param int $line The line number where the exception occurred (optional).
     */
    public function __construct(?string $message = null, int $line = 0) {
        parent::__construct($message ?? $this -> message, $line);
    }
}
