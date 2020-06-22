<?php

namespace MrEssex\FileCache\Cache\Exceptions;

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
     * @param string $key
     *
     * @return InvalidArgumentException
     */
    public static function keyIsNotAString(string $key): InvalidArgumentException
    {
        return new self(
          sprintf(
            "The specified key: %s is not a string!",
            $key
          ), 5
        );
    }

    /**
     * @param string $key
     *
     * @return InvalidArgumentException
     */
    public static function keyIsEmpty(string $key): InvalidArgumentException
    {
        return new self(
          sprintf(
            "The specified key: %s is empty!",
            $key
          ), 5
        );
    }

    /**
     * @param string $key
     *
     * @return InvalidArgumentException
     */
    public static function keyContainsInvalidCharacters(string $key): InvalidArgumentException
    {
        return new self(
          sprintf(
            "The specified key: %s must only contain [A-Z] [a-z] [0-9] [_] [.] [-] characters!",
            $key
          ), 5
        );
    }

}
