<?php

/**
 * Test: DK\ImagesManager\ImagesManager
 *
 * @testCase DKTests\ImagesManager\ImagesManagerTest
 * @author David Kudera
 */

namespace DKTests\ImagesManager;

require_once __DIR__. '/../bootstrap.php';

use DK\ImagesManager\DefaultNameResolver;
use DK\ImagesManager\ImagesManager;
use Tester\Assert;
use Nette\Utils\Image as NetteImage;
use DK\ImagesManager\INameResolver;
use DK\ImagesManager\Image;

/**
 *
 * @author David Kudera
 */
class ImagesManagerTest extends TestCase
{


	public function testLoad()
	{
		$manager = new ImagesManager(new DefaultNameResolver, __DIR__. '/../www/images', '/');
		$image = $manager->load('dots', 'black.jpg');

		Assert::same('black.jpg', $image->getName());
	}


	public function testLoad_default()
	{
		$manager = new ImagesManager(new DefaultNameResolver, __DIR__. '/../www/images', '/');
		$image = $manager->load('dots', 'pink.jpg');

		Assert::same('default.jpg', $image->getName());
	}


	public function testLoad_not_exists()
	{
		$manager = new ImagesManager(new DefaultNameResolver, __DIR__. '/../www/images', '/');

		Assert::exception(function() use ($manager) {
			$manager->load('blackness', 'pink.jpg');
		}, 'DK\ImagesManager\ImageNotExistsException', 'Image "pink.jpg" does not exists.');
	}


	public function testLoad_not_exits_and_reset_default()
	{
		$manager = new ImagesManager(new DefaultNameResolver, __DIR__. '/../www/images', '/');

		Assert::exception(function() use ($manager) {
			$manager->load('dots', 'pink.jpg', null, null, false);
		}, 'DK\ImagesManager\ImageNotExistsException', 'Image "pink.jpg" does not exists.');
	}


	public function testLoad_withoutExtension()
	{
		$manager = new ImagesManager(new DefaultNameResolver, __DIR__. '/../www/images', '/');
		$image = $manager->load('dots', 'black');

		Assert::contains($image->getName(), array('black.jpg', 'black.png'));
	}


	public function testLoad_withoutExtension_notExists()
	{
		$manager = new ImagesManager(new DefaultNameResolver, __DIR__. '/../www/images', '/');

		Assert::exception(function() use ($manager) {
			$manager->load('dots', 'red');
		}, 'DK\ImagesManager\InvalidArgumentException', 'Name must in "<name>.<extension>" format, "red" given.');
	}


	public function testLoad_customNameResolver()
	{
		$manager = new ImagesManager(new ArrayNameResolver, __DIR__. '/../www/images', '/');

		$image = $manager->load('dots', array(
			'name' => 'black',
			'extension' => 'jpg',
		));

		Assert::same('black.jpg', $image->getName());
	}


	public function testLoad_customNameResolverDefault()
	{
		$manager = new ImagesManager(new ArrayNameResolver, __DIR__. '/../www/images', '/');

		$image = $manager->load('dots', array(
			'name' => 'pink',
			'extension' => 'jpg',
		));

		Assert::same('default.jpg', $image->getName());
	}


	public function testLoad_customNameResolverRewriteDefault()
	{
		$manager = new ImagesManager(new ArrayNameResolver('black.jpg'), __DIR__. '/../www/images', '/');

		$image = $manager->load('dots', array(
			'name' => 'pink',
			'extension' => 'jpg',
		));

		Assert::same('black.jpg', $image->getName());
	}


	public function testFindImages()
	{
		$manager = new ImagesManager(new DefaultNameResolver, __DIR__. '/../www/images', '/');

		$images = array_map(function(Image $image) {
			return $image->getPath();
		}, $manager->findImages('dots'));

		$expect = array_map(function($name) use ($manager) {
			return $manager->getBasePath(). DIRECTORY_SEPARATOR. 'dots'. DIRECTORY_SEPARATOR. $name;
		}, array(
			'black.jpg',
			'black.png',
			'default.jpg',
		));

		sort($expect, SORT_STRING);
		sort($images, SORT_STRING);

		Assert::equal($expect, $images);
	}


	public function testFindThumbnails()
	{
		$manager = new ImagesManager(new DefaultNameResolver, __DIR__. '/../www/images', '/');

		$image = new Image('dots', 'black.jpg');
		$image->setBasePath(__DIR__. '/../www/images');

		$thumbnails = array_map(function(Image $image) {
			return $image->getPath();
		}, $manager->findThumbnails($image));

		$expect = array_map(function($name) use ($image) {
			return $image->getBasePath(). DIRECTORY_SEPARATOR. $image->getNamespace(). DIRECTORY_SEPARATOR. $image->getName(false). '_'. $name. '.jpg';
		}, array(
			'fit_2',
			'fill_200',
			'stretch_20x55',
		));

		sort($expect, SORT_STRING);
		sort($thumbnails, SORT_STRING);

		Assert::equal($expect, $thumbnails);
	}


	public function testUpload()
	{
		$this->lock();

		$manager = new ImagesManager(new DefaultNameResolver, __DIR__. '/../www/images', '/');

		$imageSource = new Image('dots', 'newBlack.jpg');
		$imageSource->setBasePath(__DIR__. '/../www/images');

		Assert::false($imageSource->isExists());

		$image = NetteImage::fromFile(__DIR__. '/../www/images/originalBlack.jpg');
		$imageSource = $manager->upload($image, 'dots', 'newBlack.jpg');

		Assert::true($imageSource->isExists());

		unlink($imageSource->getPath());
	}


	public function testUpload_customNameResolver()
	{
		$this->lock();

		$manager = new ImagesManager(new ArrayNameResolver, __DIR__. '/../www/images', '/');

		$imageSource = new Image('dots', 'newBlack.jpg');
		$imageSource->setBasePath(__DIR__. '/../www/images');

		Assert::false($imageSource->isExists());

		$image = NetteImage::fromFile(__DIR__. '/../www/images/originalBlack.jpg');
		$imageSource = $manager->upload($image, 'dots', array(
			'name' => 'newBlack',
			'extension' => 'jpg',
		));

		Assert::true($imageSource->isExists());

		unlink($imageSource->getPath());
	}


	public function testRemoveImage()
	{
		$this->lock();

		$manager = new ImagesManager(new DefaultNameResolver, __DIR__. '/../www/images', '/');

		copy(__DIR__. '/../www/images/originalBlack.jpg', __DIR__. '/../www/images/dots/newBlack.jpg');

		$imageSource = new Image('dots', 'newBlack.jpg');
		$imageSource->setBasePath(__DIR__. '/../www/images');

		Assert::true($imageSource->isExists());

		$manager->removeImage($imageSource);

		Assert::false($imageSource->isExists());
	}


	public function testRemoveThumbnails()
	{
		$this->lock();

		$manager = new ImagesManager(new DefaultNameResolver, __DIR__. '/../www/images', '/');

		copy(__DIR__. '/../www/images/originalBlack.jpg', __DIR__. '/../www/images/dots/newBlack.jpg');

		$imageSource = new Image('dots', 'newBlack.jpg');
		$imageSource->setBasePath(__DIR__. '/../www/images');

		Assert::true($imageSource->isExists());

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


class ArrayNameResolver implements INameResolver
{


	/** @var bool */
	private $default = false;


	/**
	 * @param bool $default
	 */
	public function __construct($default = null)
	{
		$this->default = $default;
	}


	/**
	 * @param array $name
	 * @return string
	 * @throws \Exception
	 */
	public function translateName($name)
	{
		return $name['name']. '.'. $name['extension'];
	}


	/**
	 * @param mixed $name
	 * @return string
	 */
	public function getDefaultName($name)
	{
		return $this->default;
	}

}


run(new ImagesManagerTest);
