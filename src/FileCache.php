<?php

namespace MrEssex\FileCache;

use MrEssex\FileCache\Exceptions\InvalidArgumentException;
use DateInterval;
use DateTime;
use Psr\SimpleCache\CacheInterface;

/**
 * Class FileCache
 * @package MrEssex\FileCache\Cache
 */
class FileCache
  implements CacheInterface
{

    public const CACHE_PATH = DIRECTORY_SEPARATOR . '.tmp' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
    /** @var string|null */
    protected ?string $_directoryPath;
    /** @var int */
    protected int $_ttl = 3600;

    /**
     * FileCache constructor.
     *
     * @param string|null $directoryPath
     * @param int|null    $ttl
     */
    public function __construct(string $directoryPath = null, int $ttl = null)
    {
        $this->_directoryPath = $directoryPath;

        if ($directoryPath === null) {
            // assume we are running from vendor;
            $this->_directoryPath = dirname(
                __DIR__,
                5
              ) . self::CACHE_PATH;
        }

        if ($ttl !== null) {
            $this->_ttl = $ttl;
        }

        // Try to create the directory if it doesn't exist
        if (!file_exists($this->_directoryPath) && !@mkdir($this->_directoryPath, 0777, true) && !is_dir(
            $this->_directoryPath
          )) {
            throw InvalidArgumentException::directoryDoesNotExistAndCannotBeCreated($this->_directoryPath);
        }

        if ((!is_readable($this->_directoryPath) || !is_writable($this->_directoryPath)) || (realpath(
              $this->_directoryPath
            ) === false)) {
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
        $key  = $this->_generateKey($key);
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
    private function _generateKey(string $key): string
    {
        return md5($key);
    }

    private function _validateKey(string $key): bool
    {
        if (!is_string($key)) {
            throw InvalidArgumentException::keyIsNotAString($key);
        }

        if (empty($key)) {
            throw InvalidArgumentException::keyIsEmpty($key);
        }

        if (preg_match('/[' . preg_quote('{}()/\@:;', '/') . ']/', $key)) {
            throw InvalidArgumentException::keyContainsInvalidCharacters($key);
        }

        return true;
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
        $key  = $this->_generateKey($key);
        $path = $this->_getPath($key);
        $this->_validateKey($key);

        $success = file_put_contents($path, serialize($value));

        if (!$success) {
            return false;
        }

        return touch($path, $this->_expirationToTimestamp($ttl));
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private function _getPath(string $key): string
    {
        return $this->_directoryPath . $key;
    }

    /**
     * @param int|null|string $ttl
     *
     * @return int
     */
    private function _expirationToTimestamp(?int $ttl): int
    {
        if ($ttl instanceof DateInterval) {
            $ttl = $ttl->format('%s');
        }

        if ($ttl instanceof DateTime) {
            $ttl = $ttl->getTimestamp();
        }

        $time = time();
        $ttl  = (int)$ttl;

        if ($ttl <= 0 | $ttl === null | $ttl < $time) {
            $ttl = $time + $this->_ttl;
        }

        return $ttl;
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
        $key  = $this->_generateKey($key);
        $path = $this->_getPath($key);
        $this->_validateKey($key);

        return unlink($path);
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

        foreach ($files as $file) {
            $success = unlink($this->_directoryPath . $file) && $success;
        }

        return $success;
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    A list of keys that can obtained in a single operation.
     * @param mixed    $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = null)
    {
        // TODO: Implement getMultiple() method.
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable              $values  A list of key => value pairs for a multiple-set operation.
     * @param null|int|DateInterval $ttl     Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null)
    {
        // TODO: Implement setMultiple() method.
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys)
    {
        // TODO: Implement deleteMultiple() method.
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
        $path = $this->_getPath($key);
        $keyOriginal = $key;
        $key  = $this->_generateKey($key);
        $this->_validateKey($key);

        if (! $this->_checkFileIsNotAtEndOfLife($path, $keyOriginal)) {
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
    private function _checkFileIsNotAtEndOfLife(string $path, string $key): bool {

        if(!file_exists($path)) {
            return false;
        }

        $timestamp = filemtime($path);
        $time = time();

        if ($timestamp <= $time) {
            $this->delete($key);
            return false;
        }

        return true;
    }
}
