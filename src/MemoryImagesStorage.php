<?php

namespace Carrooi\ImagesManager;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class MemoryImagesStorage implements IImagesStorage
{


	/** @var array */
	private $names = [];


	/**
	 * @param string $namespace
	 * @param string $name
	 * @return string
	 */
	public function getFullName($namespace, $name)
	{
		$name = $namespace. '/'. $name;

		if (isset($this->names[$name])) {
			return $this->names[$name];
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
		$name = $namespace. '/'. $name;

		$this->names[$name] = $fullName;
	}

}
