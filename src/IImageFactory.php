<?php

namespace Carrooi\ImagesManager;

/**
 *
 * @author David Kudera
 */
interface IImageFactory
{


	/**
	 * @param string $namespace
	 * @param string $name
	 * @return \Carrooi\ImagesManager\Image
	 */
	public function create($namespace, $name);

}
