<?php
namespace Novaxis\Core\Error;

use Novaxis\Core\Error\Exception;

class SetPathNotFoundException extends Exception {
	protected $message = 'The entered path is invalid or does not exist.';

	function __construct(?string $message = null, int $line = 0){
		parent::__construct($message ?? $this -> message, $line);
	}
}