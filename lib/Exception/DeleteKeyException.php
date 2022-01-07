<?php

declare(strict_types=1);

namespace Betorcs\Exception;

class DeleteKeyException extends \Exception
{

    function __construct(string $message = 'It was not possible to delete key', \Throwable $cause = null)
    {
        parent::__construct($message, 0, $cause);
    }
}
