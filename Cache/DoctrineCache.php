<?php

namespace Bangpound\Bridge\Drupal\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;

class DoctrineCache implements \DrupalCacheInterface
{
    private $cache;

    public function __construct($bin)
    {
        $this->cache = new FilesystemCache(conf_path() .'/cache/'. $bin);
    }

    public function get($cid)
    {
        $data = $this->cache->fetch($cid);
        if ($data) {
            $cache = new \stdClass();
            $cache->data = $data;

            return $cache;
        } else {
            return FALSE;
        }
    }

    public function getMultiple(&$cids)
    {
        $results = array();
        foreach ($cids as $cid) {
            $results[$cid] = $this->get($cid);
        }

        return array_filter($results);
    }

    public function set($cid, $data, $expire = CACHE_PERMANENT)
    {
        if ($expire === CACHE_PERMANENT) {
            $lifeTime = 0;
        } elseif ($expire === CACHE_TEMPORARY) {
            $lifeTime = variable_get('cache_lifetime', 1);
        } else {
            $lifeTime = $expire - REQUEST_TIME;
        }
        $this->cache->save($cid, $data, $lifeTime);
    }

    public function clear($cid = NULL, $wildcard = FALSE)
    {
        if (empty($cid)) {
        } else {
            if ($wildcard) {
                $this->cache->flushAll();
            } elseif (is_array($cid)) {
                // Delete in chunks when a large array is passed.
                do {
                    $this->cache->delete(array_pop($cid));
                } while (count($cid));
            } else {
                $this->cache->delete($cid);
            }
        }
    }

    public function isEmpty()
    {
        $stats = $this->cache->getStats();

        return ($stats[Cache::STATS_MEMORY_USAGE] === 0);
    }
}
