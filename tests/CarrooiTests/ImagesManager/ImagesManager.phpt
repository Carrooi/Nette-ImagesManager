<?php

/**
 * Test: Carrooi\ImagesManager\ImagesManager\ImagesManager
 *
 * @testCase CarrooiTests\ImagesManager\ImagesManagerTest
 */

namespace CarrooiTests\ImagesManager;

use Carrooi\ImagesManager\Caching\ICacheStorage;
use Carrooi\ImagesManager\Configuration;
use Carrooi\ImagesManager\Dummy\IDummyImageProvider;
use Carrooi\ImagesManager\Image\IImageFactory;
use Carrooi\ImagesManager\Image\Image;
use Carrooi\ImagesManager\ImageNotExistsException;
use Carrooi\ImagesManager\ImagesManager;
use Carrooi\ImagesManager\InvalidImageNameException;
use Carrooi\ImagesManager\Naming\INameResolver;
use Carrooi\ImagesManager\Storages\IStorage;
use Nette\Utils\Image as NetteImage;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require_once __DIR__. '/../bootstrap.php';

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class ImagesManagerTest extends TestCase
{


	public function tearDown()
	{
		\Mockery::close();
	}


	public function testUpload_notFullName()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Naming\INameResolver $nameResolver */
		$nameResolver = \Mockery::mock(INameResolver::class)
			->shouldReceive('getName')->once()->with('blue')->andReturn('blue')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('getNameResolver')->once()->with('color')->andReturn($nameResolver)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class);

		/** @var \Mockery\MockInterface|\Nette\Utils\Image $img */
		$img = \Mockery::mock(NetteImage::class);

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::exception(function() use ($manager, $img) {
			$manager->upload($img, 'color', 'blue');
		}, InvalidImageNameException::class, 'Image name must be with valid extension, blue given.');
	}


	public function testUpload()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class);

		/** @var \Mockery\MockInterface|\Nette\Utils\Image $img */
		$img = \Mockery::mock(NetteImage::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Naming\INameResolver $nameResolver */
		$nameResolver = \Mockery::mock(INameResolver::class)
			->shouldReceive('getName')->once()->with('blue')->andThrow(InvalidImageNameException::class)->getMock()
			->shouldReceive('getName')->once()->with('blue.gif')->andReturn('blue.gif')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class)
			->shouldReceive('create')->once()->with('color', 'blue.gif')->andReturn($image)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class)
			->shouldReceive('saveImage')->once()->with($img, $image, 80)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('getNameResolver')->twice()->with('color')->andReturn($nameResolver)->getMock()
			->shouldReceive('getQuality')->once()->with('color')->andReturn(80)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class);

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::same($image, $manager->upload($img, 'color', 'blue.gif'));
	}


	public function testUpload_removeOld()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$oldImage = \Mockery::mock(Image::class);

		/** @var \Mockery\MockInterface|\Nette\Utils\Image $img */
		$img = \Mockery::mock(NetteImage::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Naming\INameResolver $nameResolver */
		$nameResolver = \Mockery::mock(INameResolver::class)
			->shouldReceive('getName')->once()->with('blue')->andReturn('blue.png')->getMock()
			->shouldReceive('getName')->once()->with('blue.gif')->andReturn('blue.gif')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class)
			->shouldReceive('create')->once()->with('color', 'blue.png')->andReturn($oldImage)->getMock()
			->shouldReceive('create')->once()->with('color', 'blue.gif')->andReturn($image)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class)
			->shouldReceive('isImageExists')->once()->with($oldImage)->andReturn(true)->getMock()
			->shouldReceive('removeImage')->once()->with($oldImage)->getMock()
			->shouldReceive('saveImage')->once()->with($img, $image, 80)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('getNameResolver')->twice()->with('color')->andReturn($nameResolver)->getMock()
			->shouldReceive('getQuality')->once()->with('color')->andReturn(80)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class)
			->shouldReceive('clear')->once()->with('color', 'blue.png')->getMock()
			->shouldReceive('clearImageVersion')->once()->with('color', 'blue.png')->getMock();

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::same($image, $manager->upload($img, 'color', 'blue.gif'));
	}


	public function testUpload_removeOld_sameName()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$oldImage = \Mockery::mock(Image::class);

		/** @var \Mockery\MockInterface|\Nette\Utils\Image $img */
		$img = \Mockery::mock(NetteImage::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Naming\INameResolver $nameResolver */
		$nameResolver = \Mockery::mock(INameResolver::class)
			->shouldReceive('getName')->once()->with('blue')->andReturn('blue.gif')->getMock()
			->shouldReceive('getName')->once()->with('blue.gif')->andReturn('blue.gif')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class)
			->shouldReceive('create')->twice()->with('color', 'blue.gif')->andReturnValues([$oldImage, $image])->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class)
			->shouldReceive('isImageExists')->once()->with($oldImage)->andReturn(true)->getMock()
			->shouldReceive('removeImage')->once()->with($oldImage)->getMock()
			->shouldReceive('saveImage')->once()->with($img, $image, 80)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('getNameResolver')->twice()->with('color')->andReturn($nameResolver)->getMock()
			->shouldReceive('getQuality')->once()->with('color')->andReturn(80)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class)
			->shouldReceive('clear')->once()->with('color', 'blue.gif')->getMock()
			->shouldReceive('increaseImageVersion')->once()->with('color', 'blue.gif')->getMock();

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::same($image, $manager->upload($img, 'color', 'blue.gif'));
	}


	public function testGetImage()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Naming\INameResolver $nameResolver */
		$nameResolver = \Mockery::mock(INameResolver::class)
			->shouldReceive('getName')->once()->with('blue.gif')->andReturn('blue.gif')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class)
			->shouldReceive('create')->once()->with('color', 'blue.gif')->andReturn($image)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class)
			->shouldReceive('isImageExists')->once()->with($image)->andReturn(true)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('getNameResolver')->once()->with('color')->andReturn($nameResolver)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class);

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::same($image, $manager->getImage('color', 'blue.gif'));
	}


	public function testGetImage_noExtension()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Naming\INameResolver $nameResolver */
		$nameResolver = \Mockery::mock(INameResolver::class)
			->shouldReceive('getName')->twice()->with('blue')->andReturn('blue')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class)
			->shouldReceive('create')->twice()->with('color', 'blue.gif')->andReturn($image)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class)
			->shouldReceive('isImageExists')->twice()->with($image)->andReturn(true)->getMock()
			->shouldReceive('getFullName')->once()->with('color', 'blue')->andReturn('blue.gif')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('getNameResolver')->twice()->with('color')->andReturn($nameResolver)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class)
			->shouldReceive('getFullName')->twice()->with('color', 'blue')->andReturnValues([null, 'blue.gif'])->getMock()
			->shouldReceive('storeAlias')->once()->with('color', 'blue.gif', 'blue')->getMock();

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::same($image, $manager->getImage('color', 'blue'));
		Assert::same($image, $manager->getImage('color', 'blue'));
	}


	public function testGetImage_noExtension_notExists()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Naming\INameResolver $nameResolver */
		$nameResolver = \Mockery::mock(INameResolver::class)
			->shouldReceive('getName')->once()->with('blue')->andReturn('blue')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class)
			->shouldReceive('getFullName')->once()->with('color', 'blue')->andReturnNull()->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('getNameResolver')->once()->with('color')->andReturn($nameResolver)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class)
			->shouldReceive('getFullName')->once()->with('color', 'blue')->andReturnNull()->getMock();

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::exception(function() use ($manager) {
			$manager->getImage('color', 'blue');
		}, ImageNotExistsException::class, 'Image blue does not exists in namespace color.');
	}


	public function testGetImage_notExists()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Naming\INameResolver $nameResolver */
		$nameResolver = \Mockery::mock(INameResolver::class)
			->shouldReceive('getName')->once()->with('blue.gif')->andReturn('blue.gif')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class)
			->shouldReceive('create')->once()->with('color', 'blue.gif')->andReturn($image)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class)
			->shouldReceive('isImageExists')->once()->with($image)->andReturn(false)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('getNameResolver')->once()->with('color')->andReturn($nameResolver)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class);

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::exception(function() use ($manager) {
			$manager->getImage('color', 'blue.gif');
		}, ImageNotExistsException::class, 'Image blue.gif does not exists in namespace color.');
	}


	public function testFindImage()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Naming\INameResolver $nameResolver */
		$nameResolver = \Mockery::mock(INameResolver::class)
			->shouldReceive('getName')->once()->with('blue.gif')->andReturn('blue.gif')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class)
			->shouldReceive('create')->once()->with('color', 'blue.gif')->andReturn($image)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class)
			->shouldReceive('isImageExists')->once()->with($image)->andReturn(true)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('getNameResolver')->once()->andReturn($nameResolver)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class);

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::same($image, $manager->findImage('color', 'blue.gif'));
	}


	public function testFindImage_default()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$default = \Mockery::mock(Image::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Naming\INameResolver $nameResolver */
		$nameResolver = \Mockery::mock(INameResolver::class)
			->shouldReceive('getName')->once()->with('blue.gif')->andReturn('blue.gif')->getMock()
			->shouldReceive('getName')->once()->with('default')->andReturn('default')->getMock()
			->shouldReceive('getDefaultName')->once()->with('blue.gif')->andReturnNull()->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class)
			->shouldReceive('create')->once()->with('color', 'blue.gif')->andReturn($image)->getMock()
			->shouldReceive('create')->once()->with('color', 'default.jpg')->andReturn($default)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class)
			->shouldReceive('isImageExists')->once()->with($image)->andReturn(false)->getMock()
			->shouldReceive('isImageExists')->once()->with($default)->andReturn(true)->getMock()
			->shouldReceive('getFullName')->once()->with('color', 'default')->andReturn('default.jpg')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('getNameResolver')->times(3)->andReturn($nameResolver)->getMock()
			->shouldReceive('getDefaultImage')->once()->andReturn('default')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class)
			->shouldReceive('getFullName')->once()->with('color', 'default')->andReturnNull()->getMock()
			->shouldReceive('storeAlias')->once()->with('color', 'default.jpg', 'default')->getMock();

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::same($default, $manager->findImage('color', 'blue.gif'));
	}


	public function testFindImage_notExists()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$default = \Mockery::mock(Image::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Naming\INameResolver $nameResolver */
		$nameResolver = \Mockery::mock(INameResolver::class)
			->shouldReceive('getName')->once()->with('blue.gif')->andReturn('blue.gif')->getMock()
			->shouldReceive('getName')->once()->with('default.jpg')->andReturn('default.jpg')->getMock()
			->shouldReceive('getDefaultName')->once()->with('blue.gif')->andReturnNull()->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class)
			->shouldReceive('create')->once()->with('color', 'blue.gif')->andReturn($image)->getMock()
			->shouldReceive('create')->once()->with('color', 'default.jpg')->andReturn($default)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class)
			->shouldReceive('isImageExists')->once()->with($image)->andReturn(false)->getMock()
			->shouldReceive('isImageExists')->once()->with($default)->andReturn(false)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('getNameResolver')->times(3)->andReturn($nameResolver)->getMock()
			->shouldReceive('getDefaultImage')->once()->with('color')->andReturn('default.jpg')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class);

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::null($manager->findImage('color', 'blue.gif'));
	}


	public function testIsImageExists_true()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Naming\INameResolver $nameResolver */
		$nameResolver = \Mockery::mock(INameResolver::class)
			->shouldReceive('getName')->once()->with('blue.gif')->andReturn('blue.gif')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class)
			->shouldReceive('create')->once()->with('color', 'blue.gif')->andReturn($image)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class)
			->shouldReceive('isImageExists')->once()->with($image)->andReturn(true)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('getNameResolver')->once()->with('color')->andReturn($nameResolver)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class);

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::true($manager->isImageExists('color', 'blue.gif'));
	}


	public function testIsImageExists_false()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Naming\INameResolver $nameResolver */
		$nameResolver = \Mockery::mock(INameResolver::class)
			->shouldReceive('getName')->once()->with('blue.gif')->andReturn('blue.gif')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class)
			->shouldReceive('create')->once()->with('color', 'blue.gif')->andReturn($image)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class)
			->shouldReceive('isImageExists')->once()->with($image)->andReturn(false)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('getNameResolver')->once()->with('color')->andReturn($nameResolver)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class);

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::false($manager->isImageExists('color', 'blue.gif'));
	}


	public function testRemove()
	{
		Environment::$checkAssertions = false;

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class)
			->shouldReceive('removeImage')->once()->with($image)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class);

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		$manager->remove($image);
	}


	public function testGetDummyImageUrl()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Dummy\IDummyImageProvider $dummyProvider */
		$dummyProvider = \Mockery::mock(IDummyImageProvider::class)
			->shouldReceive('getUrl')->once()->with(40, 50, null)->andReturn('dummy.com/cat')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('isDummyEnabled')->once()->andReturn(true)->getMock()
			->shouldReceive('getDummyDisplayChance')->once()->andReturn(100)->getMock()
			->shouldReceive('getDummyCategory')->once()->andReturn(null)->getMock()
			->shouldReceive('getDummyProvider')->once()->andReturn($dummyProvider)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class);

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::same('dummy.com/cat', $manager->getDummyImageUrl('cat', 40, 50));
	}


	public function testGetDummyImageUrl_disabled()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('isDummyEnabled')->once()->andReturn(false)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class);

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::null($manager->getDummyImageUrl('cat', 40, 50));
	}


	public function testGetDummyImageUrl_noChance()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('isDummyEnabled')->once()->andReturn(true)->getMock()
			->shouldReceive('getDummyDisplayChance')->once()->andReturn(0)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class);

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::null($manager->getDummyImageUrl('cat', 40, 50));
	}


	public function testGetDummyImageUrl_fallbackSize()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Dummy\IDummyImageProvider $dummyProvider */
		$dummyProvider = \Mockery::mock(IDummyImageProvider::class)
			->shouldReceive('getUrl')->once()->with(20, 50, null)->andReturn('dummy.com/cat')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('isDummyEnabled')->once()->andReturn(true)->getMock()
			->shouldReceive('getDummyDisplayChance')->once()->andReturn(100)->getMock()
			->shouldReceive('getDummyFallbackSize')->once()->andReturn([20, 50])->getMock()
			->shouldReceive('getDummyCategory')->once()->andReturnNull()->getMock()
			->shouldReceive('getDummyProvider')->once()->andReturn($dummyProvider)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class);

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::same('dummy.com/cat', $manager->getDummyImageUrl('cat'));
	}


	public function testGetDummyImageUrl_noSize()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('isDummyEnabled')->once()->andReturn(true)->getMock()
			->shouldReceive('getDummyDisplayChance')->once()->andReturn(100)->getMock()
			->shouldReceive('getDummyFallbackSize')->once()->andReturnNull()->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class);

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::null($manager->getDummyImageUrl('cat'));
	}


	public function testGetUrl()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class)
			->shouldReceive('getNamespace')->twice()->andReturn('color')->getMock()
			->shouldReceive('getName')->once()->andReturn('image.png')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class)
			->shouldReceive('getImageUrl')->once()->with($image, null, null, null)->andReturn('localhost/image.png')->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('getResizeFlag')->once()->with('color')->andReturn(NetteImage::FIT)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class)
			->shouldReceive('getImageVersion')->once()->with('color', 'image.png')->andReturn(3)->getMock();

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		Assert::same('localhost/image.png?v=3', $manager->getUrl($image));
	}


	public function testTryStoreThumbnail()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class)
			->shouldReceive('getNamespace')->once()->andReturn('color')->getMock();

		/** @var \Mockery\MockInterface|\Nette\Utils\Image $img */
		$img = \Mockery::mock(NetteImage::class)
			->shouldReceive('resize')->once()->with(50, 100, NetteImage::EXACT)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = \Mockery::mock(IImageFactory::class);

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = \Mockery::mock(IStorage::class)
			->shouldReceive('isImageExists')->twice()->with($image, 50, 100, NetteImage::EXACT)->andReturnValues([false, true])->getMock()
			->shouldReceive('getNetteImage')->once()->with($image)->andReturn($img)->getMock()
			->shouldReceive('getNetteImage')->once()->with($image, 50, 100, NetteImage::EXACT)->andReturn($img)->getMock()
			->shouldReceive('tryStoreThumbnail')->once()->with($image, $img, 50, 100, NetteImage::EXACT, 90)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Configuration $config */
		$config = \Mockery::mock(Configuration::class)
			->shouldReceive('getQuality')->once()->with('color')->andReturn(90)->getMock();

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Caching\ICacheStorage $cache */
		$cache = \Mockery::mock(ICacheStorage::class);

		$manager = new ImagesManager($factory, $storage, $config, $cache);

		$manager->tryStoreThumbnail($image, 50, 100, NetteImage::EXACT);

		Assert::type(NetteImage::class, $manager->tryStoreThumbnail($image, 50, 100, NetteImage::EXACT));
	}

}


run(new ImagesManagerTest);