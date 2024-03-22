<?php
namespace Novaxis\Core\Cache;

/**
 * Class Cache
 *
 * A simple cache management class that stores data in a file.
 * It allows setting and retrieving key-value pairs in the cache.
 *
 * @package Novaxis\Core\Cache
 */
class Cache {
	/**
     * The path to the cache file.
     *
     * @var string
     */
	private $cacheFile;
	
	/**
     * The default cache file path if not specified.
     *
     * @var string
     */
	private $defaultCacheFilePath = __DIR__ . '/cache_data.dat';

	/**
     * Cache constructor.
     *
     * Initializes the Cache instance with the cache file path.
     *
     * @param string|null $cacheFile The path to the cache file (optional).
     */
	public function __construct(?string $cacheFile = null) {
		$this -> cacheFile = $cacheFile ?? $this -> defaultCacheFilePath;
	}

	/**
     * Set a key-value pair in the cache.
     *
     * @param string $key The key to set.
     * @param mixed $value The value to associate with the key.
     * @return bool True if the operation was successful, false otherwise.
     */
	public function set(string $key, $value): bool {
		$data = $this -> readCacheFile();
		$data[$key] = $value;

		return $this -> writeCacheFile($data);
	}

	/**
     * Get the value associated with the given key from the cache.
     *
     * @param string $key The key to retrieve the value for.
     * @return mixed|null The value associated with the key, or null if the key doesn't exist.
     */
	public function get(string $key) {
		$data = $this -> readCacheFile();

		return isset($data[$key]) ? $data[$key] : null;
	}

	/**
     * Remove the key-value pair with the given key from the cache.
     *
     * @param string $key The key to remove from the cache.
     * @return bool True if the operation was successful, false otherwise.
     */
	public function remove(string $key): bool {
		$data = $this -> readCacheFile();
		unset($data[$key]);

		return $this -> writeCacheFile($data);
	}

	/**
     * Reset the cache by removing all key-value pairs.
     *
     * @return bool True if the operation was successful, false otherwise.
     */
	public function reset(): bool {
		return $this -> writeCacheFile([]);
	}

	/**
     * Read the cache data from the cache file.
     *
     * @return array The associative array of cached data.
     */
	private function readCacheFile(): array {
		if (file_exists($this -> cacheFile)) {
			return include $this -> cacheFile;
		}

		return [];
	}

	/**
     * Write the cache data to the cache file.
     *
     * @param array $data The associative array of data to be written to the cache file.
     * @return bool True if the operation was successful, false otherwise.
     */
	private function writeCacheFile(array $data): bool {
		$content = '<?php return ' . var_export($data, true) . ';';
          
		return (bool) file_put_contents($this -> cacheFile, $content);
	}
}