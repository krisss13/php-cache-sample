<?php

namespace Krisss\Printful;

use Krisss\Printful\Interface\CacheInterface;

/**
 * FileCache
 */
class FileCache implements CacheInterface
{
    /**
     * @var string
     */
    private string $cacheDirectory;

    /**
     * @param string $cacheDirectory
     */
    public function __construct(string $cacheDirectory)
    {
        $this->cacheDirectory = $cacheDirectory;
    }

    /**
     * Get data from cache file
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key): mixed
    {
        $filename = $this->getCacheFilename($key);

        if (!file_exists($filename) || $this->isCacheExpired($filename)) {
            return null;
        }

        return unserialize(file_get_contents($filename));
    }

    /**
     * Set data in cache file
     * @param string $key
     * @param $value
     * @param int $duration
     * @return mixed
     */
    public function set(string $key, $value, int $duration): mixed
    {
        $filename = $this->getCacheFilename($key);
        file_put_contents($filename, serialize($value));
        touch($filename, time() + $duration);
        return '';
    }

    /**
     * Get cache file by file name
     * @param string $key
     * @return string
     */
    private function getCacheFilename(string $key): string
    {
        return $this->cacheDirectory . '/' . md5($key) . '.cache';
    }

    /**
     * Check if cache expired
     * @param string $filename
     * @return bool
     */
    private function isCacheExpired(string $filename): bool
    {
        return time() > filemtime($filename);
    }
}