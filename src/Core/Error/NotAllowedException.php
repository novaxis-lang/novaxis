<?php
namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

/**
 * Class NotAllowedException
 *
 * Exception class for handling cases where adding a new item is not allowed.
 *
 * @package Novaxis\Core\Error
 */
class NotAllowedException extends Exception {
    /**
     * The default error message for the exception.
     *
     * @var string
     */
    protected $message = 'Adding a new item is not allowed.';

    /**
     * NotAllowedException constructor.
     *
     * @param string|null $message The custom error message for the exception (optional).
     * @param int $line The line number where the exception occurred.
     */
    public function __construct(?string $message = null, int $line = 0){
        parent::__construct($message ?? $this->message, $line);
    }
}