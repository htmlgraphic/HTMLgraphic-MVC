<?php

class HGMemCache
{

	private static $DEV = "127.0.0.1";
	static private $instance;
	private $cache;

	private function __construct()
	{
		$this->cache = new MemcachePool();

		$servers = 0;
		if (Config::isMyDevbox())
		{
			$servers += $this->cache->addServer(self::$DEV) ? 1 : 0;
		}
		else
		{
			$servers += $this->cache->addServer("172.16.10.1") ? 1 : 0;
			$servers += $this->cache->addServer("172.16.10.2") ? 1 : 0;
		}
		if ($servers == 0)
			unset($this->cache);
	}

	private static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new EAMemCache();
		}
		return self::$instance;
	}

	public static function getStats()
	{
		return self::getInstance()->cache->getStats();
	}

	private function is_available()
	{

		$stats = $this->cache->getStats();
		return isset($stats) && is_array($stats) && count($stats);
	}

	public static function isAvailable()
	{

		return self::getInstance()->is_available();
	}

	public function valuesForKeys($keys)
	{
		if ($this->is_available())
		{
			$start = microtime();
			$start = explode(" ", $start);
			$start = $start[1] + $start[0];

			$result = $this->cache->get($keys);

			$end = microtime();
			$end = explode(" ", $end);
			$end = $end[1] + $end[0];
			$database_name = "MEMCACHE";
			Debugger::query("***MemCache GET: " . explode(",", $keys) . "***", $end - $start, __CLASS__);

			return $result;
		}
		else
			error_log("MEMCACHE IS UNAVAILABLE. PLEASE CALL MemCache::isAvailable() before using!!!!");
	}

	public function valueForKey($key)
	{
		if ($this->is_available())
		{
			$start = microtime();
			$start = explode(" ", $start);
			$start = $start[1] + $start[0];

			$result = $this->cache->get($key);

			$end = microtime();
			$end = explode(" ", $end);
			$end = $end[1] + $end[0];
			$database_name = "MEMCACHE";
			Debugger::query("***MemCache GET: $key***", $end - $start, __CLASS__);

			return $result;
		}
		else
			error_log("MEMCACHE IS UNAVAILABLE. PLEASE CALL MemCache::isAvailable() before using!!!!");
	}

	static function get($key)
	{
		if (self::isAvailable())
		{
			$start = microtime();
			$start = explode(" ", $start);
			$start = $start[1] + $start[0];

			$result = self::getInstance()->cache->get($key);

			$end = microtime();
			$end = explode(" ", $end);
			$end = $end[1] + $end[0];
			$database_name = "MEMCACHE";
			if (is_array($key))
				$keyString = "(" . implode(", ", $key) . ") Total: " . count($key);
			else
				$keyString = $key;

			Debugger::query("<span style=\"color:#02b618;\">***MemCache GET: $keyString***</span>", $end - $start, __CLASS__);

			return $result;
		}
		else
			error_log("MEMCACHE IS UNAVAILABLE. PLEASE CALL MemCache::isAvailable() before using!!!!");
	}

	public function setKeyToValue($key, $value)
	{
		if ($this->is_available())
		{
			$start = microtime();
			$start = explode(" ", $start);
			$start = $start[1] + $start[0];

			$this->cache->set($key, $value);

			$end = microtime();
			$end = explode(" ", $end);
			$end = $end[1] + $end[0];
			$database_name = "MEMCACHE";
			Debugger::query("***MemCache SET: $key***", $end - $start, __CLASS__);
		}
		else
			error_log("MEMCACHE IS UNAVAILABLE. PLEASE CALL MemCache::isAvailable() before using!!!!");
	}

	static function set($key, $value, $ttl=0)
	{
		if (self::isAvailable())
		{
			$start = microtime();
			$start = explode(" ", $start);
			$start = $start[1] + $start[0];

			self::getInstance()->cache->set($key, $value, 0, $ttl);

			$end = microtime();
			$end = explode(" ", $end);
			$end = $end[1] + $end[0];
			$database_name = "MEMCACHE";
			Debugger::query("***MemCache SET: $key***", $end - $start, __CLASS__);
		}
		else
			error_log("MEMCACHE IS UNAVAILABLE. PLEASE CALL MemCache::isAvailable() before using!!!!");
	}

	public static function increment($key, $ttl = 0)
	{
		if (self::isAvailable())
		{
			$result = self::getInstance()->cache->increment($key, 1, 1, 0); //memcache 3.0.4 docs say params 3 and 4 are default and ttl, but doesn't seem to work?
			if (!$result)
			{
				self::set($key, 1, $ttl);
				return 1;
			}
			return $result;
		}
		else
			error_log("MEMCACHE IS UNAVAILABLE. PLEASE CALL MemCache::isAvailable() before using!!!!");
	}

	private function get_stat($stat)
	{
		$memcache_obj->getStats();
		$stats = $this->cache->getStats();
		return $stats[$stat];
	}

	function getVersion()
	{
		return $this->get_stat("version");
	}

	private function sendStatsToDebugger()
	{
		$stat = (object) $this->cache->getStats();
		$stats->cacheHit = round(((real) $stat->get_hits / (real) $stat->cmd_get * 100), 2);
		$stats->cacheMiss = 100 - $stats->cacheHit . "%";
		$stats->cacheHit = $stats->cacheHit . "%";

		$stats->read = round((real) $stat->bytes_read / (1024 * 1024), 2) . " MB";
		$stats->write = round((real) $stat->bytes_written / (1024 * 1024), 2) . " MB";
		$stats->size = round((real) $stat->limit_maxbytes / (1024 * 1024), 2) . " MB";
		$stats->use = round((real) $stat->bytes / (1024 * 1024), 2) . " MB";
		$stats->items_now = number_format($stat->curr_items);
		$stats->items_total = number_format($stat->total_items);
		$stats->evictions = number_format($stat->evictions);

		Debugger::Log($stats);
	}

}
?>