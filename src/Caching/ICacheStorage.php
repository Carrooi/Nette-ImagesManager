<?php

namespace Carrooi\ImagesManager\Caching;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
interface ICacheStorage
{


	/**
	 * @param string $namespace
	 * @param string $alias
	 * @return string|null
	 */
	public function getFullName($namespace, $alias);


	/**
	 * @param string $namespace
	 * @param string $name
	 * @param string $alias
	 */
	public function storeAlias($namespace, $name, $alias);


	/**
	 * @param string $namespace
	 * @param string $name
	 */
	public function clear($namespace, $name);


	/**
	 * @param string $namespace
	 * @param string $name
	 * @return int
	 */
	public function getImageVersion($namespace, $name);


	/**
	 * @param string $namespace
	 * @param string $name
	 */
	public function clearImageVersion($namespace, $name);


	/**
	 * @param string $namespace
	 * @param string $name
	 */
	public function increaseImageVersion($namespace, $name);

}
