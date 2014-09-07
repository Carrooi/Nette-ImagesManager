<?php

/**
 * Test: DK\ImagesManager\Image
 *
 * @testCase DKTests\ImagesManager\ImageTest
 * @author David Kudera
 */

namespace DKTests\ImagesManager;

require_once __DIR__. '/../bootstrap.php';

use Tester\Assert;

/**
 *
 * @author David Kudera
 */
class ImageTest extends TestCase
{


	public function testGetPath()
	{
		$manager = $this->getManager();
		$image = $manager->load('dots', 'black.jpg');

		Assert::same($this->context->parameters['appDir']. '/../www/images/base/dots/dots_black.jpg', $image->getPath());
	}


	public function testGetPath_thumbnail()
	{
		$manager = $this->getManager();
		$image = $manager->load('dots', 'black.png')->setSize('200x50');

		Assert::same($this->context->parameters['appDir']. '/../www/images/base/dots/dots_black_fit_200x50.png', $image->getPath());
	}


	public function testGetPath_thumbnail_different_resize_flag()
	{
		$manager = $this->getManager();
		$image = $manager->load('dots', 'black.jpg')->setSize(50)->setResizeFlag('stretch');

		Assert::same($this->context->parameters['appDir']. '/../www/images/base/dots/dots_black_stretch_50.jpg', $image->getPath());
	}


	public function testIsExists_thumbnail()
	{
		$manager = $this->getManager();
		$image = $manager->load('dots', 'black.jpg')->setSize(2);

		Assert::true($image->isExists());
	}


	public function testIsExists_thumbnail_height()
	{
		$manager = $this->getManager();
		$image = $manager->load('dots', 'black.png')->setSize('50x100');

		Assert::true($image->isExists());
	}


	public function testIsExists_thumbnail_not_exists()
	{
		$manager = $this->getManager();
		$image = $manager->load('dots', 'black.jpg')->setSize(7);

		Assert::false($image->isExists());
	}


	public function testGetUrl()
	{
		$manager = $this->getManager();
		$image = $manager->load('dots', 'black.jpg');

		Assert::same('/images/base/dots/dots_black.jpg', $image->getUrl());
	}


	public function testGetUrl_absolute()
	{
		$manager = $this->getManager();
		$image = $manager->load('dots', 'black.jpg');

		Assert::same('http://localhost/images/base/dots/dots_black.jpg', $image->getUrl(true));
	}


	public function testGetUrl_thumbnail()
	{
		$manager = $this->getManager();
		$image = $manager->load('dots', 'black.jpg')->setSize(4);

		Assert::same('/images/base/dots/dots_black_fit_4.jpg', $image->getUrl());
	}


	public function testGetUrl_thumbnail_absolute()
	{
		$manager = $this->getManager();
		$image = $manager->load('dots', 'black.jpg')->setSize(4);

		Assert::same('http://localhost/images/base/dots/dots_black_fit_4.jpg', $image->getUrl(true));
	}


	public function testTryCreateThumbnail()
	{
		$manager = $this->getManager();
		$image = $manager->load('dots', 'black.jpg')->setSize(10);

		Assert::false($image->isExists());

		$image->tryCreateThumbnail();

		Assert::true($image->isExists());

		unlink($image->getPath());
	}


	public function testTryCreateThumbnail_not_thumbnail()
	{
		$manager = $this->getManager();
		$image = $manager->load('dots', 'black.jpg');

		Assert::exception(function() use ($image) {
			$image->tryCreateThumbnail();
		}, 'DK\ImagesManager\InvalidStateException', 'Can not create thumbnail for "black.jpg" image when size is not provided.');
	}

}


run(new ImageTest);