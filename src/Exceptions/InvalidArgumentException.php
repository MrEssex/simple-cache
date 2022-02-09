<?php

namespace MrEssex\FileCache\Exceptions;

use Psr\SimpleCache\InvalidArgumentException as IInvalidArgumentException;

/**
 * Class InvalidArgumentException
 * @package MrEssex\FileCache\Cache\Exceptions
 */
class InvalidArgumentException
  extends \InvalidArgumentException
  implements IInvalidArgumentException
{

  /**
   * @param string $directory
   *
   * @return InvalidArgumentException
   */
  public static function directoryDoesNotExistAndCannotBeCreated(string $directory): InvalidArgumentException
  {
    return new self(
      sprintf(
        "The Directory: %s does not exist. or isn't readable, isn't writable and/or can't be created!",
        $directory
      ), 5
    );
  }

  /**
   * @param mixed $key
   *
   * @return InvalidArgumentException
   */
  public static function keyIsNotAString($key): InvalidArgumentException
  {
    return new self(
      sprintf(
        "The specified key: %s is not a string!",
        $key
      ), 500
    );
  }

  /**
   * @param mixed|null $key
   *
   * @return InvalidArgumentException
   */
  public static function keyIsEmpty($key): InvalidArgumentException
  {
    return new self(
      sprintf(
        "The specified key: %s is empty!",
        $key
      ), 500
    );
  }

  /**
   * @param mixed $key
   *
   * @return InvalidArgumentException
   */
  public static function keyContainsInvalidCharacters($key): InvalidArgumentException
  {
    return new self(
      sprintf(
        "The specified key: %s must only contain [A-Z] [a-z] [0-9] [_] [.] [-] characters!",
        $key
      ), 500
    );
  }

  /**
   * @return InvalidArgumentException
   */
  public static function failedToCache(): InvalidArgumentException
  {
    return new self(
      "Failed to add to cache", 500
    );
  }

  /**
   * @return InvalidArgumentException
   */
  public static function invalidTraversableArgument(): InvalidArgumentException
  {
    return new self(
      "Argument 1 must be an array or a Traversable", 500
    );
  }

}
