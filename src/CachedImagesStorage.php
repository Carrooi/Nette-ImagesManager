<?php

namespace Carrooi\ImagesManager;

use Nette\Caching\IStorage;
use Nette\Caching\Cache;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class CachedImagesStorage extends MemoryImagesStorage
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
	 * @param string $name
	 * @return string
	 */
	public function getFullName($namespace, $name)
	{
		$name = parent::getFullName($namespace, $name);

		if ($name === null) {
			$fullName = $this->cache->load('name/'. $namespace. '/'. $name);

			if ($fullName) {
				parent::storeAlias($namespace, $name, $fullName);
				return $fullName;
			}
		}

		return null;
	}

	/**
	 * @param string $namespace
	 * @param string $name
	 * @param string $fullName
	 */
	public function storeAlias($namespace, $name, $fullName)
	{
		parent::storeAlias($namespace, $name, $fullName);

		$this->cache->save('name/'. $namespace. '/'. $name, $fullName, array(
			Cache::TAGS => array($namespace. '/'. $fullName),
		));
	}


	/**
	 * @param string $namespace
	 * @param string $fullName
	 */
	public function clear($namespace, $fullName)
	{
		parent::clear($namespace, $fullName);

		$this->cache->clean(array(
			Cache::TAGS => array($namespace. '/'. $fullName),
		));
	}


	/**
	 * @param string $namespace
	 * @param string $name
	 * @return string
	 */
	public function getDefault($namespace, $name)
	{
		$default = parent::getDefault($namespace, $name);

		if ($default === null) {
			$default = $this->cache->load('default/'. $namespace. '/'. $name);

			if ($default) {
				parent::storeDefault($namespace, $name, $default);
				return $default;
			}
		}

		return null;
	}


	/**
	 * @param string $namespace
	 * @param string $name
	 * @param string $default
	 */
	public function storeDefault($namespace, $name, $default)
	{
		parent::storeDefault($namespace, $name, $default);

		$this->cache->save('default/'. $namespace. '/'. $name, $default);
	}

}
