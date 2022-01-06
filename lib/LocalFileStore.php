<?php

declare(strict_types=1);

use Betorcs\FileStore;
use Betorcs\FileStoreException;

class LocalFileStore implements FileStore
{

    private const PREFIX = 'st-';
    private $dir;

    function __construct(string $dir)
    {
        $this->dir = $dir;
    }

    public function store(string $content, int $exp): string
    {
        $key = $this->createKey($exp);

        $path = $this->getFilePath($key);

        try {
            $handler = fopen($path, 'w');
            fwrite($handler, $content);
            fclose($handler);
            return $key;
        } catch (\Exception $e) {
            throw new FileStoreException('Can not store content', $e);
        }
    }

    public function restore(string $key): string
    {
        try {
            $path = $this->getFilePath($key);
            if (!file_exists($path)) {
                throw new \Exception("No file found for given key");
            }
            if ($this->isExpired($key)) {
                throw new \Exception('File expired');
            }
            $stream = fopen($path, 'r');
            $content = fread($stream, filesize($path));
            fclose($stream);
            return $content;
        } catch (\Exception $e) {
            throw new FileStoreException($e->getMessage(), $e);
        }
    }

    public function delete(string $key): void {
        $path = $this->getFilePath($key);
        if (file_exists($path) && !unlink($path)) {
            throw new FileStoreException("It was not possible to delete key");
        }
    }

    /**
     * Deletes all keys contents.
     *
     * @return void
     */
    public function clean(): void {
        $d = dir($this->dir);
        while (false !== ($entry = $d->read())) {
            $key = basename($entry);
            if (strpos($key, self::PREFIX) === 0) {
                $this->delete($key);
            }
        }
        $d->close();
    }

    public function deleteAllExpired(): void {
        $d = dir($this->dir);
        while (false !== ($entry = $d->read())) {
            $key = basename($entry);
            if (strpos($key, self::PREFIX) === 0 && $this->isExpired($key)) {
                $this->delete($key);
            }
        }
        $d->close();
    }

    public function exists(string $key): bool {
        $path = $this->getFilePath($key);
        return file_exists($path);
    }

    private function isExpired(string $key): bool
    {
        if (preg_match('/\d{8,}/', $key, $matches) && count($matches) === 1) {
            $ts = $matches[0];
            $date = new DateTime("@$ts");
            $now = new DateTime('now', new DateTimeZone('UTC'));
            return $date < $now;
        }

        return false;
    }

    private function createKey(int $exp): string
    {
        $day = 60 * 60 * 24;
        $exp = $exp > 0 ? $exp : $day;
        $date = new DateTime('now', new DateTimeZone('UTC'));
        $date->add(new DateInterval("PT${exp}S"));
        return uniqid(self::PREFIX . $date->getTimestamp() . '-');
    }

    private function getFilePath($key)
    {
        return "$this->dir/$key";
    }
}
