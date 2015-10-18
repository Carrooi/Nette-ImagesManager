<?php

namespace CarrooiTests\Mocks;

use Carrooi\ImagesManager\Dummy\IDummyImageProvider;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class DummyImageProviderMock implements IDummyImageProvider
{


	/**
	 * @param int $width
	 * @param int $height
	 * @param string|null $category
	 * @return string
	 */
	public function getUrl($width, $height, $category = null)
	{
		return 'http://dummy-image.com/'. $width. '/'. $height. '/'. ($category ? $category : '');
	}

}
