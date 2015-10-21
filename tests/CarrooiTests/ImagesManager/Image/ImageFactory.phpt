<?php

/**
 * Test: Carrooi\ImagesManager\ImagesManager\Image\ImageFactory
 *
 * @testCase CarrooiTests\ImagesManager\Image\ImageFactoryTest
 */

namespace CarrooiTests\ImagesManager\Image;

use Carrooi\ImagesManager\Image\Image;
use Carrooi\ImagesManager\Image\ImageFactory;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__. '/../../bootstrap.php';

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class ImageFactoryTest extends TestCase
{


	public function testCreate()
	{
		$factory = new ImageFactory;

		$image = $factory->create('color', 'blue.jpg');

		Assert::type(Image::class, $image);
		Assert::same('color', $image->getNamespace());
		Assert::same('blue.jpg', $image->getName());
	}

}


run(new ImageFactoryTest);