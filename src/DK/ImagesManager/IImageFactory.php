<?php

namespace DK\ImagesManager;

/**
 *
 * @author David Kudera
 */
interface IImageFactory
{


	/**
	 * @param string $namespace
	 * @param string $name
	 * @return \DK\ImagesManager\Image
	 */
	public function create($namespace, $name);

}
 