<?php

/**
 * Test: Carrooi\ImagesManager\ImagesManager\Image\Image
 *
 * @testCase CarrooiTests\ImagesManager\Image\ImageTest
 */

namespace CarrooiTests\ImagesManager\Image;

use Carrooi\ImagesManager\Image\Image;
use Carrooi\ImagesManager\InvalidImageNameException;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__. '/../../bootstrap.php';

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class ImageTest extends TestCase
{


	public function testInvalidName()
	{
		Assert::exception(function() {
			new Image('color', 'blue');
		}, InvalidImageNameException::class, 'Image name must be with valid extension, blue given.');
	}


	public function testGetName()
	{
		$image = new Image('color', 'blue.jpg');

		Assert::same('color', $image->getNamespace());
		Assert::same('blue.jpg', $image->getName());
	}

}


run(new ImageTest);