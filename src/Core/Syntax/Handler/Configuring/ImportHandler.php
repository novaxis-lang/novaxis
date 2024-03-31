<?php
namespace Novaxis\Core\Syntax\Handler\Configuring;

use Novaxis\Core\Path;
use Novaxis\Core\Runner;
use Novaxis\Config\Utils;
use Novaxis\Core\Error\Exception;
use Novaxis\Core\Syntax\Token\PathTokens;
use Novaxis\Core\Syntax\Token\ImportTokens;
use Novaxis\Core\Error\FileNotFoundException;
use Novaxis\Core\Syntax\Handler\Configuring\ImportDictFile;
use Novaxis\Core\Syntax\Handler\Configuring\AliasNamingrules;

class ImportHandler {
	use PathTokens;
	use ImportTokens;

	private Path $Path;
	private AliasNamingrules $AliasNamingrules;
	public string $req_shell_path;
	public string $pattern = "/^\s*(publicly|privately)?\s*(import)\s{1,}(\"(.*?)\")\s{1,}(\-\>\s*(JSON|NOVAXIS|NOV|NVX|YAML|TOML)\s{1,})?(as\s{1,}((.{1,})|\?))\s*$/i";
	public string $formats_pattern = "/^(JSON|NOVAXIS|NOV|NVX|YAML|TOML)$/i";

	public function __construct(string $req_shell_path) {
		$this -> Path = new Path;
		$this -> AliasNamingrules = new AliasNamingrules;
		$this -> req_shell_path = $req_shell_path;
	}
	
	public function isImporting(string $line) {
		return preg_match($this -> pattern, $line);
	}

	public function isImportingPublicly(string $line) {
		preg_match($this -> pattern, $line, $matches);
		if (trim($matches[1]) == self::IMPORTING_PUBLICLY_KEYWORD) {
			return true;
		}
		return false;
	}

	public function isImportingPrivately(string $line) {
		preg_match($this -> pattern, $line, $matches);
		if (trim($matches[1]) == self::IMPORTING_PRIVATELY_KEYWORD || empty(trim($matches[1]))) {
			return true;
		}
		return false;
	}

	public function getTargetFile(string $line) {
		preg_match($this -> pattern, $line, $matches);
		if (isset($matches[4])) {
			return trim($this -> req_shell_path) . "/" . $matches[4];
		}
		else {
			// Exception
		}
	}

	public function getTargetFormat(string $line) {
		preg_match($this -> pattern, $line, $matches);
		if (isset($matches[6]) && !empty($matches[6])) {
			return strtoupper($matches[6]);
		}
		else {
			$ext = trim(pathinfo($this -> getTargetFile($line), PATHINFO_EXTENSION));
			preg_match($this -> formats_pattern, $ext, $matches);
			if (isset($matches[0]) && !empty($matches[0])) {
				return strtoupper($ext);
			}
			else {
				// Exception
				throw new Exception;
			}
		}
	}

	public function getAliasName(string $line) {
		preg_match($this -> pattern, $line, $matches);
		if (isset($matches[8])) {
			$aliasName = $matches[8];
			if (trim($aliasName) == self::DEFAULT_ALIAS) {
				$aliasName = $this -> getDefaultAlias($this -> getTargetFile($line));
			}
			$this -> isAliasNameValid($aliasName);
			return trim($aliasName);
		}
		else {
			// Exception
		}
	}

	public function isAliasNameValid(string $name) {
		$this -> AliasNamingrules -> isValid($name, true);
	}

	public function getDefaultAlias(string $filename) {
		return explode("." /* file extension */, basename($filename))[0];
	}
	
	public function handle(string $line, $path = null) {
		$filename = $this -> getTargetFile($line);
		$format = $this -> getTargetFormat($line);
		if ($format == "NOVAXIS") {
			return $this -> NovaxisHandler($line, $path);
		}
		else if ($format == "JSON") {
			$ImportDictFile = new ImportDictFile($filename);
			$visibility = $this -> isImportingPublicly($line) ? 'PUBLIC' : 'PROTECTED';
			$data = $ImportDictFile -> handle($visibility, $path . self::PATH_SEPARATOR . $this -> getAliasName($line));
			return $data;
		}
		else {
			// Exception
		}
	}

	public function NovaxisHandler(string $line, $path = null) {
		$filename = $this -> getTargetFile($line);
		if (file_exists($filename)) {
			$Runner = new Runner($filename, null, $this -> req_shell_path);
			if (isset($Runner -> execute(false)[0])) {
				$data = $Runner -> execute(false)[0];
				$newData = array();
				foreach ($data as $key => $value) {
					if ($this -> isImportingPublicly($line)) {}
					else if ($this -> isImportingPrivately($line)) {
						$value["visibility"] = "protected";
					}
					$newData[$this -> Path -> clean($path . self::PATH_SEPARATOR . $this -> getAliasName($line) . self::PATH_SEPARATOR . "$key")] = $value;
				}
				return $newData;
			}
			else {
				throw new Exception;
			}
		}
		else {
			throw new FileNotFoundException;
		}
	}
}