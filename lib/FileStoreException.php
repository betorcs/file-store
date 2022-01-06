<?php

namespace Betorcs;

class FileStoreException extends \Exception {

    function __construct(string $message, \Throwable $cause = null)
    {
        parent::__construct($message, 0, $cause);
    }

}