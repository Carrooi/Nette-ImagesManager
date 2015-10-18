<?php

namespace Carrooi\ImagesManager\Dummy;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class LorempixelDummyImageProvider implements IDummyImageProvider
{


	/**
	 * @param int $width
	 * @param int $height
	 * @param string|null $category
	 * @return string
	 */
	public function getUrl($width, $height, $category = null)
	{
		return 'http://lorempixel.com/'
			. $width. '/'. $height. '/'
			. ($category ? $category. '/' : '');
	}

}
