<?php
namespace Novaxis\Core\Syntax\Handler\H_Class;

use Novaxis\Core\Path;

/**
 * Class MaximumElements
 * Manages the maximum number of elements allowed for specific paths.
 *
 * @package Novaxis\Core\Syntax\Handler\H_Class
 */
class MaximumElements {
	/**
	 * Instance of the Path class to manage paths.
	 *
	 * @var Path
	 */
	private Path $Path;

	/**
	 * An array to store maximum element counts for specific paths.
	 *
	 * @var array
	 */
	private array $items;

	/**
	 * MaximumElements constructor.
	 * 
	 * Initializes the Path instance and the items array.
	 */
	public function __construct() {
		$this -> Path = new Path;
		$this -> items = [];
	}

	/**
	 * Adds a maximum element count for a specific path.
	 *
	 * @param string $path The path to associate with the maximum element count.
	 * @param int $maximum The maximum number of elements allowed.
	 *
	 * @return $this
	 */
	public function addItem(string $path, int $maximum) {
		$path = $this -> Path -> clean($path);
		$this -> items[$path] = $maximum;

		return $this;
	}

	/**
	 * Retrieves the maximum element count for a specific path.
	 *
	 * @param string $path The path to retrieve the maximum element count for.
	 *
	 * @return mixed The maximum element count for the specified path.
	 */
	public function getItem(string $path) {
		$path = $this -> Path -> clean($path);

		return $this -> items[$path];
	}

	/**
	 * Retrieves all items with their associated maximum element counts.
	 *
	 * @return array An array containing path-to-maximum mappings.
	 */
	public function getItems() {
		return $this -> items;
	}

	/**
	 * Decrements the maximum element count for a specific path, indicating a chance is used.
	 *
	 * @param string $path The path to decrement the maximum element count for.
	 *
	 * @return $this
	 */
	public function loseAChance(string $path) {
		$path = $this -> Path -> clean($path);
		
		if (isset($this -> items[$path])) {
			if ($this -> items[$path] > 0) {
				$this -> items[$path] --;
			}
		}

		return $this;
	}

	/**
	 * Checks if adding a new item is allowed based on the remaining maximum element count for a specific path.
	 *
	 * @param string $path The path to check for maximum element count.
	 *
	 * @return bool True if adding a new item is allowed, false otherwise.
	 */
	public function allowed(string $path) {
		$path = $this -> Path -> clean($path);
		
		if (isset($this -> items[$path])) {
			if ($this -> items[$path] > 0) {
				return true;
			}
		}
		else {
			return true;
		}

		return false;
	}
}