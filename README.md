### FILE STORE

it's a shave implementation of file store based in keys, useful to handle temporary files.

### Installation

Install using [composer](https://getcomposer.org/).

```shell
composer require betorcs/file-store
```

### Example usage

```php

$fileContent = ...

$baseDir = '/tmp';

$fileStore = new \Betorcs\LocalFileStore($baseDir);

// Saves a file content with expiration of 120 seconds, then returns a key.
$key = $fileStore->store($fileContent, 120);


// Deletes all expired contents
$fileStore->deleteAllExpired();

// Checkes if exists a non expired content
if ($fileStore->exists($key)) 
{
    // It's TRUE if exists
}

// Retrieve a content from given key, if it exists and non expired
$content = $fileStore->restore($key);

// Deletes all contents
$fileStore->clean();


```
