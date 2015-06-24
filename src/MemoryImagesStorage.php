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
		if (!isset($this->names[$namespace])) {
			$this->names[$namespace] = [];
		}

		if (isset($this->names[$namespace][$name])) {
			return $this->names[$namespace][$name];
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
		if (!isset($this->names[$namespace])) {
			$this->names[$namespace] = [];
		}

		$this->names[$namespace][$name] = $fullName;
	}


	/**
	 * @param string $namespace
	 * @param string $fullName
	 */
	public function clear($namespace, $fullName)
	{
		if (!isset($this->names[$namespace])) {
			return;
		}

		$remove = [];

		foreach ($this->names[$namespace] as $name => $savedFullName) {
			if ($savedFullName === $fullName) {
				$remove[] = $name;
			}
		}

		foreach ($remove as $name) {
			unset($this->names[$namespace][$name]);
		}
	}

}
