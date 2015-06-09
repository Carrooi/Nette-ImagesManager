<?php

/**
 * Test: Carrooi\ImagesManager\Image
 *
 * @testCase CarrooiTests\ImagesManager\ImageTest
 * @author David Kudera
 */

namespace CarrooiTests\ImagesManager;

require_once __DIR__. '/../bootstrap.php';

use Carrooi\ImagesManager\Image;
use Tester\Assert;

/**
 *
 * @author David Kudera
 */
class ImageTest extends TestCase
{


	public function testGetPath()
	{
		$image = new Image('dots', 'black.jpg');
		$image->setBasePath('/var/www/images');

		Assert::same('/var/www/images/dots/black.jpg', $image->getPath());
	}


	public function testGetPath_thumbnail()
	{
		$image = new Image('dots', 'black.jpg');
		$image->setBasePath('/var/www/images');
		$image->setSize('200x50');

		Assert::same('/var/www/images/dots/black_fit_200x50.jpg', $image->getPath());
	}


	public function testGetPath_thumbnail_different_resize_flag()
	{
		$image = new Image('dots', 'black.jpg');
		$image->setBasePath('/var/www/images');
		$image->setSize(50);
		$image->setResizeFlag('stretch');

		Assert::same('/var/www/images/dots/black_stretch_50.jpg', $image->getPath());
	}


	public function testIsExists_thumbnail()
	{
		$image = new Image('dots', 'black.jpg');
		$image->setBasePath(__DIR__. '/../www/images');
		$image->setSize(2);

		Assert::true($image->isExists());
	}


	public function testIsExists_thumbnail_height()
	{
		$image = new Image('dots', 'black.png');
		$image->setBasePath(__DIR__. '/../www/images');
		$image->setSize('50x100');

		Assert::true($image->isExists());
	}


	public function testIsExists_thumbnail_not_exists()
	{
		$image = new Image('dots', 'black.jpg');
		$image->setBasePath(__DIR__. '/../www/images');
		$image->setSize(7);

		Assert::false($image->isExists());
	}


	public function testGetUrl()
	{
		$image = new Image('dots', 'black.jpg');

		Assert::same('/dots/black.jpg', $image->getUrl());
	}


	public function testGetUrl_absolute()
	{
		$image = new Image('dots', 'black.jpg');
		$image->setHost('http://localhost');

		Assert::same('http://localhost/dots/black.jpg', $image->getUrl(true));
	}


	public function testGetUrl_thumbnail()
	{
		$image = new Image('dots', 'black.jpg');
		$image->setSize(4);

		Assert::same('/dots/black_fit_4.jpg', $image->getUrl());
	}


	public function testGetUrl_thumbnail_absolute()
	{
		$image = new Image('dots', 'black.jpg');
		$image->setHost('http://localhost');
		$image->setSize(4);

		Assert::same('http://localhost/dots/black_fit_4.jpg', $image->getUrl(true));
	}


	public function testTryCreateThumbnail()
	{
		$this->lock();

		$image = new Image('dots', 'black.jpg');
		$image->setBasePath(__DIR__. '/../www/images');
		$image->setSize(10);

		Assert::false($image->isExists());

		$image->tryCreateThumbnail();

		Assert::true($image->isExists());

		unlink($image->getPath());
	}


	public function testTryCreateThumbnail_not_thumbnail()
	{
		$image = new Image('dots', 'black.jpg');

		Assert::exception(function() use ($image) {
			$image->tryCreateThumbnail();
		}, 'Carrooi\ImagesManager\InvalidStateException', 'Can not create thumbnail for "black.jpg" image when size is not provided.');
	}

}


run(new ImageTest);
