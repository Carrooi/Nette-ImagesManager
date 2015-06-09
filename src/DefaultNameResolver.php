<?php

namespace Carrooi\ImagesManager;

use Nette\Object;

/**
 *
 * @author David Kudera
 */
class DefaultNameResolver extends Object implements INameResolver
{

	/**
	 * @param string $name
	 * @return string
	 */
	public function translateName($name)
	{
		return $name;
	}


	/**
	 * @param mixed $name
	 * @return string
	 */
	public function getDefaultName($name)
	{
		return null;
	}

}
