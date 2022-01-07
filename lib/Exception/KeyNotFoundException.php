<?php

declare(strict_types=1);

namespace Betorcs\Exception;

class KeyNotFoundException extends \Exception
{

    function __construct(string $message = 'No file found for given key', \Throwable $cause = null)
    {
        parent::__construct($message, 0, $cause);
    }
}
