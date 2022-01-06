### FILE STORE

it's a shave implementation of file store based in keys.

```php

$data = 'foo';

$key = $this->fileStore->store($data, 120);

$content = $this->fileStore->restore($key);

echo $content;
// foo
```