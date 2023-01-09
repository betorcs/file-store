<?php

declare(strict_types=1);

namespace Betorcs;

use Betorcs\Exception\DeleteKeyException;
use Betorcs\Exception\FileExpiredException;
use Betorcs\Exception\KeyNotFoundException;
use Betorcs\Exception\FileStoreException;
use Betorcs\FileStore;

/**
 * Implements FileStore in local file system, using the base directory to store the files.
 */
class LocalFileStore implements FileStore
{

    private const DEFAULT_PREFIX = 'fs$-';
    private $baseDir;
    private $prefix;

    /**
     * Default constructor.
     *
     * @param string $baseDir base directory when files should be saved.
     * @param string $prefix the prefix to be used in the temporary name file, 
     *                       if it's null the {@code DEFAULT_PREFIX} will be used.
     */
    function __construct(string $baseDir, string $prefix = null)
    {
        $this->baseDir = $baseDir;
        $this->prefix = $prefix ?? self::DEFAULT_PREFIX
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function restore(string $key): string
    {
        try {
            $path = $this->getFilePath($key);
            if (!file_exists($path)) {
                throw new KeyNotFoundException();
            }
            if ($this->isExpired($key)) {
                throw new FileExpiredException();
            }
            $handler = fopen($path, 'r');
            $content = fread($handler, filesize($path));
            fclose($handler);
            return $content;
        } catch (FileExpiredException $e) {
            throw $e;
        } catch (KeyNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new FileStoreException($e->getMessage(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): void
    {
        $path = $this->getFilePath($key);
        if (file_exists($path) && !unlink($path)) {
            throw new DeleteKeyException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clean(): void
    {
        $d = dir($this->baseDir);
        while (false !== ($entry = $d->read())) {
            $key = basename($entry);
            if (strpos($key, $this->prefix) === 0) {
                $this->delete($key);
            }
        }
        $d->close();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAllExpired(): void
    {
        $d = dir($this->baseDir);
        while (false !== ($entry = $d->read())) {
            $key = basename($entry);
            if (strpos($key, $this->prefix) === 0 && $this->isExpired($key)) {
                $this->delete($key);
            }
        }
        $d->close();
    }

    /**
     * Deletes all keys contents.
     *
     * @return void
     */
    public function exists(string $key): bool
    {
        $path = $this->getFilePath($key);
        return file_exists($path);
    }

    private function isExpired(string $key): bool
    {
        if (preg_match('/\d{8,}/', $key, $matches) && count($matches) === 1) {
            $ts = $matches[0];
            $date = new \DateTime("@$ts");
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            return $date < $now;
        }

        return false;
    }

    private function createKey(int $exp): string
    {
        $day = 60 * 60 * 24;
        $exp = $exp > 0 ? $exp : $day;
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $date->add(new \DateInterval("PT${exp}S"));
        return uniqid($this->prefix . $date->getTimestamp() . '-');
    }

    private function getFilePath($key)
    {
        return "$this->baseDir/$key";
    }
}
