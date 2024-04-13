<?php

namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

/**
 * Class MultidimensionalIndexingWithStringException
 *
 * Exception class representing an error when multidimensional indexing is attempted with strings.
 *
 * @package Novaxis\Core\Error
 */
class MultidimensionalIndexingWithStringException extends Exception {
    /**
     * The default error message for the multidimensional indexing with string exception.
     *
     * @var string
     */
    protected $message = "Multidimensional indexing with strings is not allowed.";

    /**
     * MultidimensionalIndexingWithStringException constructor.
     *
     * @param string|null $message The custom error message for the exception (optional).
     * @param int $line The line number where the exception occurred (optional).
     */
    public function __construct(?string $message = null, int $line = 0) {
        parent::__construct($message ?? $this -> message, $line);
    }
}
