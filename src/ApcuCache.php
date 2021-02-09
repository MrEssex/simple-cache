<?php

namespace MrEssex\FileCache;

use DateInterval;
use Psr\SimpleCache\InvalidArgumentException;

class ApcuCache extends AbstractCache
{

  protected array $_cacheKeys = [];

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
  public function get($key, $default = null)
  {
    $key = $this->_generateKey($key);
    $this->_validateKey($key);

    if(!apcu_exists($key))
    {
      return $default;
    }

    $value = apcu_fetch($key, $success);
    if($success)
    {
      return $value;
    }

    return $default;
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
  public function set($key, $value, $ttl = null)
  {
    $key = $this->_generateKey($key);
    $this->_validateKey($key);

    if(apcu_exists($key))
    {
      apcu_delete($key);
    }

    if(!$ttl)
    {
      $ttl = $this->_ttl;
    }

    if(apcu_add($key, $value, $ttl))
    {
      $this->_cacheKeys[] = $key;
      return true;
    }

    return false;
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
  public function delete($key)
  {
    $key = $this->_generateKey($key);
    $this->_validateKey($key);

    unset($this->_cacheKeys[$key]);

    return apcu_delete($key);
  }

  /**
   * Wipes clean the entire cache's keys.
   *
   * @return bool True on success and false on failure.
   */
  public function clear()
  {
    if(!$this->_cacheKeys)
    {
      return true;
    }

    foreach($this->_cacheKeys as $cacheKey)
    {
      unset($this->_cacheKeys[$cacheKey]);
      if(!apcu_delete($cacheKey))
      {
        return false;
      }
    }

    return true;
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
  public function has($key)
  {
    $key = $this->_generateKey($key);
    $this->_validateKey($key);

    return apcu_exists($key);
  }
}
