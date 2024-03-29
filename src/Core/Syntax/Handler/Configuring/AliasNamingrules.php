<?php
namespace Novaxis\Core\Syntax\Handler\Configuring;

use Novaxis\Core\Error\NamingRuleException;

class AliasNamingrules {
	private string $pattern = "/^[a-zA-Z0-9_]*$/";
	private array $used_names = ["self"];

	public function isValid(string $input, bool $throw = false): bool {
		$result = !in_array(trim($input), $this -> used_names) && preg_match($this -> pattern, $input) && !empty($input);
		
		if ($throw && !$result) {
			throw new NamingRuleException;
		}

		return $result;
	}
}