<?php
namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

/**
 * Class VariableInterpolationException
 *
 * Exception class for handling errors related to variable interpolation.
 *
 * @package Novaxis\Core\Error
 */
class VariableInterpolationException extends Exception {
    /**
     * the default error message for the exception.
     * 
     * @var string
     */
    protected $message = 'Variable interpolation is not allowed due to variable visibility.';

    /**
     * VariableInterpolationException constructor.
     *
     * @param string|null $message The custom error message for the exception (optional).
     * @param int $line The line number where the exception occurred.
     */
    public function __construct(?string $message = null, int $line = 0) {
        parent::__construct($message ?? $this -> message, $line);
    }
}