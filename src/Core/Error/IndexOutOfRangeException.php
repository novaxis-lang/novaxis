<?php
namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

/**
 * Class IndexOutOfRangeException
 *
 * Exception class representing an error when an index is out of range.
 *
 * @package Novaxis\Core\Error
 */
class IndexOutOfRangeException extends Exception {
    /**
     * The default error message for the index out of range exception.
     *
     * @var string
     */
    protected $message = 'Index out of range.';

    /**
     * IndexOutOfRangeException constructor.
     *
     * @param string|null $message The custom error message for the exception (optional).
     * @param int $line The line number where the exception occurred (optional).
     */
    public function __construct(?string $message = null, int $line = 0) {
        parent::__construct($message ?? $this -> message, $line);
    }
}
