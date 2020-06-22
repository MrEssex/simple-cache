<?php

namespace MrEssex\FileCache\Tests\Cache;

use MrEssex\FileCache\FileCache;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class FileCacheTest
 * @package MrEssex\FileCache\Tests\Cache
 */
class FileCacheTest
  extends TestCase
{

    /** @var string */
    protected string $_dir;

    /** @var FileCache */
    protected FileCache $_cache;

    /**
     * @throws InvalidArgumentException
     */
    public function testSet(): void
    {
        $time     = time();
        $contents = "Under Construction " . $time;

        if ($this->_cache->has('index')) {
            $this->assertFileExists($this->_dir . md5("index"));
            $this->assertStringContainsString('Under Construction', $this->_cache->get('index'));
        } else {
            $this->_cache->set("index", $contents);
            $this->assertFileExists($this->_dir . md5("index"));
            $this->assertStringContainsString(
              'Under Construction',
              $this->_cache->get("index"),
              "The contents of the cache is correct available"
            );
        }
    }

    /**
     * Test that when we clear the cache we clear all the files
     */
    public function testClear(): void
    {
        $contents = "Under Construction " . time();

        $this->_cache->set("index", $contents);
        $this->_cache->set("about", $contents);
        $this->_cache->set("blog", $contents);
        $this->_cache->set("contact", $contents);

        $this->assertFileExists($this->_dir . md5("index"));
        $this->assertFileExists($this->_dir . md5("about"));
        $this->assertFileExists($this->_dir . md5("blog"));
        $this->assertFileExists($this->_dir . md5("contact"));

        $this->_cache->clear();

        $this->assertFileDoesNotExist($this->_dir . md5("index"));
        $this->assertFileDoesNotExist($this->_dir . md5("about"));
        $this->assertFileDoesNotExist($this->_dir . md5("blog"));
        $this->assertFileDoesNotExist($this->_dir . md5("contact"));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testHas(): void
    {
        $time     = time();
        $contents = "Under Construction " . $time;
        $this->_cache->set("index", $contents);

        $this->assertFileExists($this->_dir . md5("index"));
        $this->assertTrue($this->_cache->has("index"));
    }

    /**
     * PHPUnti SetUp
     */
    protected function setUp(): void
    {
        $this->_dir   = dirname(__DIR__, 1) . FileCache::CACHE_PATH;
        $this->_cache = new FileCache($this->_dir);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        //$this->_cache->clear();
        parent::tearDown();
    }

}
