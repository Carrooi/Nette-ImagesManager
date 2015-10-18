<?php

namespace Carrooi\ImagesManager\Naming;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
interface INameResolver
{


	/**
	 * @param mixed $imageName
	 * @return string
	 */
	public function getName($imageName);


	/**
	 * @param mixed $imageName
	 * @return string|null
	 */
	public function getDefaultName($imageName);

}
