<?php

namespace Carrooi\ImagesManager\Image;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class ImageFactory implements IImageFactory
{


	/**
	 * @param string $namespace
	 * @param string $name
	 * @return \Carrooi\ImagesManager\Image\Image
	 */
	public function create($namespace, $name)
	{
		return new Image($namespace, $name);
	}

}
