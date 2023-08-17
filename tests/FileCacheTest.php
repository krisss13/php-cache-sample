<?php

use Krisss\Printful\FileCache;
use PHPUnit\Framework\TestCase;

class FileCacheTest extends TestCase
{
    private const CACHE_DIR = 'tests/cache';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        if (!is_dir(self::CACHE_DIR)) {
            mkdir(self::CACHE_DIR);
        }
    }

    /**
     * It ensures that each test starts with a clean and empty cache directory.
     * @return void
     */
    protected function tearDown(): void
    {
        array_map('unlink', glob(self::CACHE_DIR . '/*'));
        rmdir(self::CACHE_DIR);
    }

    /**
     * test if Set And Get is possibly
     * @return void
     */
    public function testSetAndGet(): void
    {
        $cache = new FileCache(self::CACHE_DIR);
        $key = 'test_key';
        $value = 'test_value';
        $duration = 300;

        $cache->set($key, $value, $duration);
        $cachedValue = $cache->get($key);

        $this->assertEquals($value, $cachedValue);
    }

    /**
     * Test if Expired Cache Returns Null
     * @return void
     */
    public function testExpiredCacheReturnsNull(): void
    {
        $cache = new FileCache(self::CACHE_DIR);
        $key = 'test_key';
        $value = 'test_value';
        $duration = 1;

        $cache->set($key, $value, $duration);
        sleep(2);

        $cachedValue = $cache->get($key);

        $this->assertNull($cachedValue);
    }

    /**
     * Test if Non-Existing Key Returns Null
     * @return void
     */
    public function testNonExistingKeyReturnsNull(): void
    {
        $cache = new FileCache(self::CACHE_DIR);
        $key = 'non_existing_key';

        $cachedValue = $cache->get($key);

        $this->assertNull($cachedValue);
    }
}