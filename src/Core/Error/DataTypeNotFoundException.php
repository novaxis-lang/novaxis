<?php
namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

class DataTypeNotFoundException extends Exception {
    /**
     * The default error message for the exception.
     *
     * @var string
     */
    protected $message = 'The datatype does not exist in the configuration.';

    /**
     * DataTypeNotFoundException constructor.
     *
     * @param string|null $message The custom error message for the exception (optional).
     * @param int $line The line number where the exception occurred.
     */
    public function __construct(?string $message = null, int $line = 0) {
        parent::__construct($message ?? $this -> message, $line);
    }
}
