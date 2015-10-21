<?php

namespace Carrooi\ImagesManager\Caching;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class CachedStorage implements ICacheStorage
{


	const CACHE_NAMESPACE = 'Carrooi.ImagesManager';


	/** @var \Nette\Caching\Cache */
	private $cache;


	/**
	 * @param \Nette\Caching\IStorage $storage
	 */
	public function __construct(IStorage $storage)
	{
		$this->cache = new Cache($storage, self::CACHE_NAMESPACE);
	}


	/**
	 * @param string $namespace
	 * @param string $alias
	 * @return string|null
	 */
	public function getFullName($namespace, $alias)
	{
		return $this->cache->load('name/'. $namespace. '/'. $alias);
	}


	/**
	 * @param string $namespace
	 * @param string $name
	 * @param string $alias
	 */
	public function storeAlias($namespace, $name, $alias)
	{
		$this->cache->save('name/'. $namespace. '/'. $alias, $name, [
			Cache::TAGS => [$namespace. '/'. $name],
		]);
	}


	/**
	 * @param string $namespace
	 * @param string $name
	 */
	public function clear($namespace, $name)
	{
		$this->cache->clean([
			Cache::TAGS => [$namespace. '/'. $name],
		]);
	}

}
