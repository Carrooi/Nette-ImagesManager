<?php

namespace Carrooi\ImagesManager\Naming;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class DefaultNameResolver implements INameResolver
{


	/**
	 * @param mixed $imageName
	 * @return string
	 */
	public function getName($imageName)
	{
		return (string) $imageName;
	}


	/**
	 * @param mixed $imageName
	 * @return string|null
	 */
	public function getDefaultName($imageName)
	{
		return null;
	}

}
