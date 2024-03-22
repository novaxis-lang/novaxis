<?php
namespace Novaxis\Core\Syntax\Handler\Variable;

use Novaxis\Core\Path;
use Novaxis\Core\Syntax\Token\VariableTokens;

/**
 * Manages variable visibility styles and accessibility.
 */
class VisibilitySyntax {
	use VariableTokens;

	/**
	 * Styles array for variable visibility and accessibility.
	 *
	 * @var array
	 */
	private array $stylesArray = array(
		/* [import by anyclass, import in the current father, user access] */
		"public" => [true, true, true],
		"protected" => [true, true, false],
		"inherited" => [true, false, false],
		"private" => [false, false, true],
		"restricted" => [false, true, true]
	);

	/**
	 * Instance of the Path class for managing hierarchical paths.
	 *
	 * @var Path
	 */
	private Path $Path;
	
	/**
	 * Constructor for the VisibilitySyntax class.
	 */
	public function __construct() {
		$this -> Path = new Path;
	}

	/**
	 * Get visibility styles and settings.
	 *
	 * @param string|null $specific Specific style to retrieve settings for.
	 * @return array Visibility styles and their settings.
	 */
	public function styles(?string $specific = null): array {
		if ($specific) {
			if (in_array(strtolower(trim($specific)), array_keys($this -> stylesArray))) {
				return $this -> stylesArray[strtolower(trim($specific))];
			}
		}

		return $this -> stylesArray;
	}

	/**
	 * Check if a variable with a certain visibility is interpolatable between paths.
	 *
	 * Checks if a variable with the given visibility can be interpolated between two paths.
	 *
	 * @param string $visibility The visibility style of the variable.
	 * @param string $firstPath The first path to compare.
	 * @param string $secondPath The second path to compare.
	 * @param bool $final Indicates if the second path is final.
	 * @return bool True if the variable is interpolatable, false otherwise.
	 */
	public function isInterpolatableIn(string $visibility, string $firstPath, string $secondPath, bool $final = false): bool {
		$firstPathCall = $this -> Path -> setFullPath($firstPath) -> getParent();
		$secondPathCall = ($final === false) ? $this -> Path -> setFullPath($secondPath) -> getParent() : $secondPath;

		if ($firstPathCall == $secondPathCall && $this -> styles($visibility)[1] === true) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a variable is interpolatable outside based on visibility.
	 *
	 * @param string $visibility The visibility style of the variable.
	 * @return bool True if the variable is interpolatable outside, false otherwise.
	 */
	public function isInterpolatableOut(string $visibility) {
		if ($this -> styles($visibility)[0] === true) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a variable is displayable based on visibility.
	 *
	 * @param string $visibility The visibility style of the variable.
	 * @return bool True if the variable is displayable, false otherwise.
	 */
	public function isDisplayable(string $visibility) {
		if ($this -> styles($visibility)[2] === true) {
			return true;
		}

		return false;
	}
	
	/**
	 * Determine if a variable can fit between paths based on visibility.
	 *
	 * @param string $visibility The visibility style of the variable.
	 * @param string $firstPath The first path to compare.
	 * @param string $secondPath The second path to compare.
	 * @param bool $final Indicates if the second path is final.
	 * @return ?bool True if the variable fits between paths, false otherwise.
	 */
	public function fit(string $visibility, $firstPath, $secondPath, bool $final = false) {
		$firstPathCall = $this -> Path -> setFullPath($firstPath) -> getParent();
		$secondPathCall = ($final === false) ? $this -> Path -> setFullPath($secondPath) -> getParent() : $secondPath;

		if ($firstPathCall == $secondPathCall) { // in the same father
			return $this -> isInterpolatableIn($visibility, $firstPath, $secondPath, $final);
		}
		else if ($firstPathCall != $secondPathCall) { // out of the father
			return $this -> isInterpolatableOut($visibility);
		}
	}

	/**
	 * Find visibility keywords that indicate removal.
	 *
	 * @return array Visibility keywords that indicate removal.
	 */
	public function findRemoversWords() {
		$filteredKeys = array_keys(array_filter($this->stylesArray, function($values) {
			return $values[2] === false;
		}));
	
		$visibilityKeywords = array_map(function($value) {
			return self::VISIBILITY_KEYWORDS[$value];
		}, $filteredKeys);
	
		return $visibilityKeywords;
	}
	
	/**
	 * Remove items from an array based on specific visibility keywords.
	 *
	 * @param array $array The array of items to be filtered.
	 * @return array The filtered array after removal.
	 */
	public function remover(array $array) {
		$value = array_keys(array_filter($array, function($values){
			return in_array(trim(strtolower($values['visibility'])), array_map('strtolower', $this -> findRemoversWords()));
		}));

		foreach ($value as $key) {
			unset($array[$key]);
		}

		return $array;
	}
}