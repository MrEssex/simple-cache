<?php

namespace MrEssex\FileCache;

use DateInterval;
use DateTime;
use MrEssex\FileCache\Exceptions\InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;

/**
 * Class AbstractCache
 * @package MrEssex\FileCache
 */
abstract class AbstractCache implements CacheInterface
{

  /**
   * @param string $key
   *
   * @return string
   */
  protected function _generateKey(string $key): string
  {
    return md5($key);
  }

  /**
   * @param mixed $key
   *
   * @return bool
   */
  protected function _validateKey($key): bool
  {
    if(!is_string($key))
    {
      throw InvalidArgumentException::keyIsNotAString($key);
    }

    if(empty($key))
    {
      throw InvalidArgumentException::keyIsEmpty($key);
    }

    if(preg_match('/[' . preg_quote('{}()/\@:;', '/') . ']/', $key))
    {
      throw InvalidArgumentException::keyContainsInvalidCharacters($key);
    }

    return true;
  }

  /**
   * @param int|null|string $ttl
   *
   * @return int
   */
  protected function _expirationToTimestamp(?int $ttl): int
  {
    if($ttl instanceof DateInterval)
    {
      $ttl = $ttl->format('%s');
    }
    else if($ttl instanceof DateTime)
    {
      $ttl = $ttl->getTimestamp();
    }

    $time = time();
    $ttl = (int)$ttl;

    if($ttl <= 0 | $ttl === null | $ttl < $time)
    {
      $ttl = $time + $this->_ttl;
    }

    return $ttl;
  }

  /**
   * Obtains multiple cache items by their unique keys.
   *
   * @param iterable $keys    A list of keys that can obtained in a single operation.
   * @param mixed    $default Default value to return for keys that do not exist.
   *
   * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as
   *                  value.
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
}
