<?php

/**
 * Test: Carrooi\ImagesManager\ImagesManager\Storages\FileSystemStorage
 *
 * @testCase CarrooiTests\ImagesManager\Storages\FileSystemStorageTest
 */

namespace CarrooiTests\ImagesManager\Storages;

use Carrooi\ImagesManager\Image\Image;
use Carrooi\ImagesManager\Storages\FileSystemStorage;
use Nette\Utils\Image as NetteImage;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require_once __DIR__. '/../../bootstrap.php';

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class FileSystemStorageTest extends TestCase
{


	/** @var array */
	private $testThumbnails = [
		'_w5_h10_f2',
		'_w5_h10',
		'_w5',
		'_h10',
	];


	public function setUp()
	{
		Environment::lock('fs-storage', TEMP_DIR. '/..');
	}


	public function tearDown()
	{
		$files = glob(__DIR__. '/data/color/*.gif');
		unset($files[array_search(__DIR__. '/data/color/blue.gif', $files)]);

		foreach ($files as $file) {
			unlink($file);
		}

		\Mockery::close();
	}


	public function testIsImageExists()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class)
			->shouldReceive('getNamespace')->once()->andReturn('color')->getMock()
			->shouldReceive('getName')->twice()->andReturn('blue.gif')->getMock();

		$storage = new FileSystemStorage([
			'basePath' => __DIR__. '/data',
			'baseUrl' => '',
		]);

		Assert::true($storage->isImageExists($image));
	}


	public function testIsImageExists_not()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class)
			->shouldReceive('getNamespace')->once()->andReturn('color')->getMock()
			->shouldReceive('getName')->twice()->andReturn('white.gif')->getMock();

		$storage = new FileSystemStorage([
			'basePath' => __DIR__. '/data',
			'baseUrl' => '',
		]);

		Assert::false($storage->isImageExists($image));
	}


	public function testSaveImage()
	{
		Environment::$checkAssertions = false;

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class)
			->shouldReceive('getNamespace')->once()->andReturn('color')->getMock()
			->shouldReceive('getName')->twice()->andReturn('blue.gif')->getMock();

		/** @var \Mockery\MockInterface|\Nette\Utils\Image $img */
		$img = \Mockery::mock(NetteImage::class)
			->shouldReceive('save')->once()->with(__DIR__. '/data/color/blue.gif', 90)->getMock();

		$storage = new FileSystemStorage([
			'basePath' => __DIR__. '/data',
			'baseUrl' => '',
		]);

		$storage->saveImage($img, $image, 90);
	}


	public function testRemove()
	{
		$this->createTestImages('red');
		$this->createTestImages('green');

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class)
			->shouldReceive('getNamespace')->times(3)->andReturn('color')->getMock()
			->shouldReceive('getName')->times(6)->andReturn('red.gif')->getMock();

		$storage = new FileSystemStorage([
			'basePath' => __DIR__. '/data',
			'baseUrl' => '',
		]);

		$storage->removeImage($image);

		Assert::false(is_file(__DIR__. '/data/color/red.gif'));
		foreach ($this->testThumbnails as $thumbnail) {
			Assert::false(is_file(__DIR__. '/data/color/red'. $thumbnail. '.gif'));
		}

		Assert::true(is_file(__DIR__. '/data/color/green.gif'));
		foreach ($this->testThumbnails as $thumbnail) {
			Assert::true(is_file(__DIR__. '/data/color/green'. $thumbnail. '.gif'));
		}
	}


	public function testGetFullName_notExists()
	{
		$storage = new FileSystemStorage([
			'basePath' => __DIR__. '/data',
			'baseUrl' => '',
		]);

		Assert::null($storage->getFullName('color', 'red'));
	}


	public function testGetFullName()
	{
		$storage = new FileSystemStorage([
			'basePath' => __DIR__. '/data',
			'baseUrl' => '',
		]);

		Assert::same('blue.gif', $storage->getFullName('color', 'blue'));
	}

	public function testGetImageUrl()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class)
			->shouldReceive('getNamespace')->twice()->andReturn('color')->getMock()
			->shouldReceive('getName')->times(4)->andReturn('red.gif')->getMock();

		$storage = new FileSystemStorage([
			'basePath' => __DIR__. '/data',
			'baseUrl' => '',
		]);

		Assert::same('/color/red.gif', $storage->getImageUrl($image));
		Assert::same('/color/red_w5_h10_f0.gif', $storage->getImageUrl($image, 5, 10, NetteImage::FIT));
	}


	public function testGetNetteImage()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class)
			->shouldReceive('getNamespace')->once()->andReturn('color')->getMock()
			->shouldReceive('getName')->twice()->andReturn('blue.gif')->getMock();

		$storage = new FileSystemStorage([
			'basePath' => __DIR__. '/data',
			'baseUrl' => '',
		]);

		$img = $storage->getNetteImage($image);

		Assert::type(NetteImage::class, $img);
	}


	public function testTryStoreThumbnail()
	{
		Environment::$checkAssertions = false;

		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Image\Image $image */
		$image = \Mockery::mock(Image::class)
			->shouldReceive('getNamespace')->once()->andReturn('color')->getMock()
			->shouldReceive('getName')->twice()->andReturn('blue.gif')->getMock();

		/** @var \Mockery\MockInterface|\Nette\Utils\Image $thumbnail */
		$thumbnail = \Mockery::mock(NetteImage::class)
			->shouldReceive('save')->once()->with(__DIR__. '/data/color/blue_w100_h50_f'. NetteImage::EXACT. '.gif', 80)->getMock();

		$storage = new FileSystemStorage([
			'basePath' => __DIR__. '/data',
			'baseUrl' => '',
		]);

		$storage->tryStoreThumbnail($image, $thumbnail, 100, 50, NetteImage::EXACT, 80);
	}


	/**
	 * @param string $image
	 */
	private function createTestImages($image)
	{
		$dir = __DIR__. '/data/color';

		copy("$dir/blue.gif", "$dir/$image.gif");
		Assert::true(is_file("$dir/$image.gif"));

		foreach ($this->testThumbnails as $thumbnail) {
			copy("$dir/blue.gif", "$dir/$image$thumbnail.gif");
			Assert::true(is_file("$dir/$image$thumbnail.gif"));
		}
	}

}


(new FileSystemStorageTest)->run();
