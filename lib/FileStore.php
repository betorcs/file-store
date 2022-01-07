<?php

declare(strict_types=1);

namespace Betorcs;

interface FileStore
{

    /**
     * Saves files and returns a key.
     * @param string $content content file should be saved.
     * @param int $exp the expiration time in seconds.
     * @return string key that represents that saved file.
     */
    public function store(string $content, int $exp): string;

    /**
     * Returns file content from given $key.
     * @param string $key File key.
     * @return File content.
     */
    public function restore(string $key): string;

    /**
     * Deletes content from given $key.
     *
     * @param string $key refers to content want to delete.
     * @return void
     */
    public function delete(string $key): void;

    /**
     * Deletes all expired keys.
     *
     * @return void
     */
    public function deleteAllExpired(): void;

    /**
     * Checks if given $key exists.
     *
     * @param string $key
     * @return boolean
     */
    public function exists(string $key): bool;

    /**
     * Deletes all keys contents.
     *
     * @return void
     */
    public function clean(): void;
}
