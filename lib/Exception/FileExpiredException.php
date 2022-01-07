<?php

declare(strict_types=1);

namespace Betorcs\Exception;

class FileExpiredException extends \Exception
{

    function __construct(string $message = 'File expired', \Throwable $cause = null)
    {
        parent::__construct($message, 0, $cause);
    }
}
