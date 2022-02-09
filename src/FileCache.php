<?php

namespace MrEssex\FileCache;

use DateInterval;
use MrEssex\FileCache\Exceptions\InvalidArgumentException;
use function file_exists;
use function realpath;

/**
 * Class FileCache
 *
 * @package MrEssex\FileCache
 */
class FileCache extends AbstractCache
{
  public const CACHE_PATH = DIRECTORY_SEPARATOR . '.tmp' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;

  /** @var string|null */
  protected ?string $_directoryPath;

  /**
   * FileCache constructor.
   *
   * @param string|null $directoryPath
   */
  public function __construct(string $directoryPath = null)
  {
    $this->_directoryPath = $directoryPath;

    if($directoryPath === null)
    {
      // assume we are running from vendor;
      $this->_directoryPath = dirname(__DIR__, 4) . self::CACHE_PATH;
    }

    // Try to create the directory if it doesn't exist
    if(!file_exists($this->_directoryPath) && !@mkdir($this->_directoryPath, 0777, true) && !is_dir(
        $this->_directoryPath
      ))
    {
      throw InvalidArgumentException::directoryDoesNotExistAndCannotBeCreated($this->_directoryPath);
    }

    if((realpath($this->_directoryPath) === false) || (!is_readable($this->_directoryPath) || !is_writable(
          $this->_directoryPath
        )))
    {
      throw InvalidArgumentException::directoryDoesNotExistAndCannotBeCreated($this->_directoryPath);
    }
  }

  /**
   * Fetches a value from the cache.
   *
   * @param string $key     The unique key of this item in the cache.
   * @param mixed  $default Default value to return if the key does not exist.
   *
   * @return mixed The value of the item from the cache, or $default in case of cache miss.
   *
   * @throws InvalidArgumentException
   *   MUST be thrown if the $key string is not a legal value.
   */
  public function get($key, $default = false)
  {
    $key = $this->_generateKey($key);
    $path = $this->_getPath($key);
    $this->_validateKey($key);

    $content = unserialize(file_get_contents($path), [false]);

    return $content ?: $default;
  }

  /**
   * @param string $key
   *
   * @return string
   */
  protected function _getPath(string $key): string
  {
    return $this->_directoryPath . $key;
  }

  /**
   * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
   *
   * @param string                $key    The key of the item to store.
   * @param mixed                 $value  The value of the item to store, must be serializable.
   * @param null|int|DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
   *                                      the driver supports TTL then the library may set a default value
   *                                      for it or let the driver take care of that.
   *
   * @return bool True on success and false on failure.
   *
   * @throws InvalidArgumentException
   *   MUST be thrown if the $key string is not a legal value.
   */
  public function set($key, $value, $ttl = null): bool
  {
    $key = $this->_generateKey($key);
    $path = $this->_getPath($key);
    $this->_validateKey($key);

    if(!$ttl)
    {
      $ttl = $this->_ttl;
    }

    $success = file_put_contents($path, serialize($value));

    if(!$success)
    {
      return false;
    }

    return touch($path, $this->_expirationToTimestamp($ttl));
  }

  /**
   * Wipes clean the entire cache's keys.
   *
   * @return bool True on success and false on failure.
   */
  public function clear(): bool
  {
    $files = array_diff(scandir($this->_directoryPath), ['.', '..', '.*']);
    $success = true;

    foreach($files as $file)
    {
      $success = unlink($this->_directoryPath . $file) && $success;
    }

    return $success;
  }

  /**
   * Determines whether an item is present in the cache.
   *
   * NOTE: It is recommended that has() is only to be used for cache warming type purposes
   * and not to be used within your live applications operations for get/set, as this method
   * is subject to a race condition where your has() will return true and immediately after,
   * another script can remove it making the state of your app out of date.
   *
   * @param string $key The cache item key.
   *
   * @return bool
   *
   * @throws InvalidArgumentException
   *   MUST be thrown if the $key string is not a legal value.
   */
  public function has($key): bool
  {
    $keyOriginal = $key;
    $key = $this->_generateKey($key);
    $this->_validateKey($key);
    $path = $this->_getPath($key);

    if(!$this->_checkFileIsNotAtEndOfLife($path, $keyOriginal))
    {
      return false;
    }

    return file_exists($path);
  }

  /**
   * Check to see if the key TTL hasn't ran dry.
   * Deletes the file if the file has expired
   *
   * @param string $path
   *
   * @param string $key
   *
   * @return bool
   */
  protected function _checkFileIsNotAtEndOfLife(string $path, string $key): bool
  {
    if(!file_exists($path))
    {
      return false;
    }

    $timestamp = filemtime($path);
    $time = time();

    if($timestamp <= $time)
    {
      $this->delete($key);

      return false;
    }

    return true;
  }

  /**
   * Delete an item from the cache by its unique key.
   *
   * @param string $key The unique cache key of the item to delete.
   *
   * @return bool True if the item was successfully removed. False if there was an error.
   *
   * @throws InvalidArgumentException
   *   MUST be thrown if the $key string is not a legal value.
   */
  public function delete($key): bool
  {
    $key = $this->_generateKey($key);
    $path = $this->_getPath($key);
    $this->_validateKey($key);

    return unlink($path);
  }
}
