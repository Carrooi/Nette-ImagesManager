<?php

namespace Carrooi\ImagesManager;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
interface IImagesStorage
{


	/**
	 * @param string $namespace
	 * @param string $name
	 * @return string
	 */
	public function getFullName($namespace, $name);


	/**
	 * @param string $namespace
	 * @param string $name
	 * @param string $fullName
	 */
	public function storeAlias($namespace, $name, $fullName);

}
