<?php
namespace NOVAXIS\Core\Syntax\Datatype;

use Novaxis\Core\Path;
use Novaxis\Core\Syntax\Token\PathTokens;
use Novaxis\Core\Syntax\Token\ClassTokens;

/**
 * The InheritanceType class represents a data structure to handle inheritance paths and their associated datatypes.
 *
 * @package Novaxis\Core\Syntax\Datatype
 */
class InheritanceType {
	use PathTokens;
	use ClassTokens;

	/**
	 * Array to store the items with inheritance path and datatype mappings.
	 *
	 * @var array
	 */
	private array $items = [];

	/**
	 * Instance of the Path class to manage paths.
	 *
	 * @var Path
	 */
	private Path $Path;

	/**
	 * Constructor for the InheritanceType class.
	 */
	public function __construct() {
		$this -> Path = new Path;
	}
	
	/**
	 * Adds an item with an inheritance path and datatype to the collection.
	 *
	 * @param string $path The inheritance path.
	 * @param string $datatype The associated datatype.
	 * @return $this
	 */
	public function addItem(string $path, string $datatype) {
		$this -> items[$this -> Path -> clean($path)] = trim(ucfirst($datatype));

		return $this;
	}

	/**
	 * Retrieves the datatype associated with the given inheritance path.
	 *
	 * @param string $path The inheritance path.
	 * @return string|null The datatype associated with the path or null if not found.
	 */
	public function getItem(string $path) {
		$path = $this -> Path -> clean($path);
		$this -> Path -> setFullPath($path);
		
		foreach (range(0, substr_count($path, self::PATH_SEPARATOR)) as $rounds) {
			if (in_array($this -> Path -> getFullPath(), array_keys($this -> items)) ) {
				return $this -> items[$this -> Path -> getFullPath()];
			}
			
			$this -> Path -> backward();
		}
		
		if (isset($this -> items['']) && empty($this -> Path -> getFullPath()) and !isset($ret)) {
			return $this -> items[''];
		}

		return null;
	}

	/**
	 * Gets all items with inheritance path and datatype mappings.
	 *
	 * @return array The items with inheritance path and datatype mappings.
	 */
	public function getItems() {
		return $this -> items;
	}

	/**
	 * Checks if the provided datatype contains the unset keyword.
	 *
	 * @param string $datatype The datatype to check.
	 * @return bool True if the datatype contains the unset keyword, false otherwise.
	 */
	public function isUnsetKeyword($datatype) {
		if (self::ANYCASE_UNSET_CLASSBOX_KEYWORD === true) {
			if (trim(strtolower($datatype)) === trim(strtolower(self::UNSET_CLASSBOX_KEYWORD))) {
				return true;
			}
		}
		else if (self::ANYCASE_UNSET_CLASSBOX_KEYWORD === false) {
			if (trim($datatype) === trim(self::UNSET_CLASSBOX_KEYWORD)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Unsets an item based on its path.
	 *
	 * @param string|null $path The path of the item to unset. If null, the root item is unset.
	 */
	public function unset(?string $path = null) {
		if ($path === null) {
			unset($this -> items['']);
		}
		else {
			unset($this -> items[trim($path)]);
		}
	}
}