<?php

/**
 * Test: DK\ImagesManager\ImagesManager
 *
 * @testCase DKTests\ImagesManager\ImagesManagerTest
 * @author David Kudera
 */

namespace DKTests\ImagesManager;

require_once __DIR__. '/../bootstrap.php';

use Nette\Utils\Image as NetteImage;
use Tester\Assert;
use DK\ImagesManager\Image;

/**
 *
 * @author David Kudera
 */
class ImagesManagerTest extends TestCase
{


	public function testGetResizeFlag()
	{
		$manager = $this->getManager();

		Assert::same('fit', $manager->getResizeFlag('dots'));
	}


	public function testGetResizeFlag_custom()
	{
		$manager = $this->getManager();

		Assert::same('stretch', $manager->getResizeFlag('colors'));
	}


	public function testGetDefault()
	{
		$manager = $this->getManager();

		Assert::same('default.jpg', $manager->getDefault('dots'));
	}


	public function testGetDefault_custom()
	{
		$manager = $this->getManager();

		Assert::same('white.png', $manager->getDefault('colors'));
	}


	public function testGetDefault_random()
	{
		$manager = $this->getManager();

		Assert::contains($manager->getDefault('lines'), array(
			'white.png', 'black.png'
		));
	}


	public function testGetDefault_list()
	{
		$manager = $this->getManager();

		Assert::contains($manager->getDefault('squares'), array(
			'white.png', 'black.png'
		));
	}


	public function testGetQuality()
	{
		$manager = $this->getManager();

		Assert::null($manager->getQuality('dots'));
	}


	public function testGetQuality_custom()
	{
		$manager = $this->getManager();

		Assert::same(100, $manager->getQuality('colors'));
	}


	public function testLoad()
	{
		$manager = $this->getManager();
		$image = $manager->load('dots', 'black.jpg');

		Assert::same('black.jpg', $image->getName());
	}


	public function testLoad_default()
	{
		$manager = $this->getManager();
		$image = $manager->load('dots', 'pink.jpg');

		Assert::same('default.jpg', $image->getName());
	}


	public function testLoad_null()
	{
		$manager = $this->getManager();
		$image = $manager->load('dots', null);

		Assert::same('default.jpg', $image->getName());
	}


	public function testLoad_not_exists()
	{
		$manager = $this->getManager();

		Assert::exception(function() use ($manager) {
			$manager->load('blackness', 'pink.jpg');
		}, 'DK\ImagesManager\ImageNotExistsException', 'Image "pink.jpg" does not exists.');
	}


	public function testLoad_not_exits_and_reset_default()
	{
		$manager = $this->getManager();

		Assert::exception(function() use ($manager) {
			$manager->load('dots', 'pink.jpg', null, null, false);
		}, 'DK\ImagesManager\ImageNotExistsException', 'Image "pink.jpg" does not exists.');
	}


	public function testGetList()
	{
		$manager = $this->getManager();
		$list = $manager->getList('colors', 'best');

		$images = array_map(function(Image $image) {
			return $image->getNamespace(). '/' .$image->getName();
		}, $list);

		$manager->getList('colors', 'best');

		Assert::equal(array(
			'colors/black.jpg',
			'colors/pink.png',
		), $images);
	}


	public function testGetList_namespace_not_exists()
	{
		$manager = $this->getManager();

		Assert::exception(function() use ($manager) {
			$manager->getList('someNamespace', 'best');
		}, 'DK\ImagesManager\InvalidArgumentException', 'Images namespace "someNamespace" is not registered.');
	}


	public function testGetList_not_exists()
	{
		$manager = $this->getManager();

		Assert::exception(function() use ($manager) {
			$manager->getList('colors', 'unknown');
		}, 'DK\ImagesManager\InvalidArgumentException', 'Images list "unknown" is not registered in "colors" namespace.');
	}


	public function testFindImages()
	{
		$manager = $this->getManager();

		$images = array_map(function(Image $image) {
			return $image->getPath();
		}, $manager->findImages('dots'));

		$expect = array_map(function($name) use ($manager) {
			return $manager->getBasePath(). DIRECTORY_SEPARATOR. 'dots'. DIRECTORY_SEPARATOR. 'dots_'. $name;
		}, array(
			'black.jpg',
			'black.png',
			'default.jpg',
		));

		Assert::equal($expect, $images);
	}


	public function testFindThumbnails()
	{
		$manager = $this->getManager();
		$image = $manager->load('dots', 'black.jpg');

		$thumbnails = array_map(function(Image $image) {
			return $image->getPath();
		}, $manager->findThumbnails($image));

		$expect = array_map(function($name) use ($image) {
			return $image->getBasePath(). DIRECTORY_SEPARATOR. $image->getNamespace(). DIRECTORY_SEPARATOR. $image->getNamespace(). '_'. $image->getName(false). '_'. $name. '.jpg';
		}, array(
			'fit_2',
			'fill_200',
			'stretch_20x55',
		));

		Assert::equal($expect, $thumbnails);
	}


	public function testUpload()
	{
		$manager = $this->getManager();
		$imageSource = $manager->createImage('dots', 'newBlack.jpg');
		$image = NetteImage::fromFile(__DIR__. '/../www/images/originalBlack.jpg');

		Assert::false($imageSource->isExists());

		$imageSource = $manager->upload($image, 'dots', 'newBlack.jpg');

		Assert::true($imageSource->isExists());

		unlink($imageSource->getPath());
	}


	public function testRemoveImage()
	{
		$manager = $this->getManager();
		$image = NetteImage::fromFile(__DIR__. '/../www/images/originalBlack.jpg');

		$imageSource = $manager->upload($image, 'dots', 'newBlack.jpg');

		Assert::true($imageSource->isExists());

		$manager->removeImage($imageSource);

		Assert::false($imageSource->isExists());
	}


	public function testRemoveThumbnails()
	{
		$manager = $this->getManager();
		$image = NetteImage::fromFile(__DIR__. '/../www/images/originalBlack.jpg');

		$imageSource = $manager->upload($image, 'dots', 'newBlack.jpg');

		$thumbnails = array(
			$manager->load('dots', 'newBlack.jpg', 2),
			$manager->load('dots', 'newBlack.jpg', 3),
			$manager->load('dots', 'newBlack.jpg', 4),
			$manager->load('dots', 'newBlack.jpg', 5),
		);

		foreach ($thumbnails as $thumbnail) {		/** @var $thumbnail \DK\ImagesManager\Image */
			Assert::true($thumbnail->isExists());
		}

		$manager->removeThumbnails($imageSource);

		foreach ($thumbnails as $thumbnail) {		/** @var $thumbnail \DK\ImagesManager\Image */
			Assert::false($thumbnail->isExists());
		}

		$manager->removeImage($imageSource);
	}

}


run(new ImagesManagerTest);