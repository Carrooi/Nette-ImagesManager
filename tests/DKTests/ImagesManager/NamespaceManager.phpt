<?php

/**
 * Test: DK\ImagesManager\NamespaceManager
 *
 * @testCase DKTests\ImagesManager\NamespaceManagerTest
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
class NamespaceManagerTest extends TestCase
{


	public function testGetResizeFlag()
	{
		$namespace = $this->getManager()->getNamespace('dots');

		Assert::same('fit', $namespace->getResizeFlag());
	}


	public function testGetResizeFlag_custom()
	{
		$namespace = $this->getManager()->getNamespace('colors');

		Assert::same('stretch', $namespace->getResizeFlag());
	}


	public function testGetDefault()
	{
		$namespace = $this->getManager()->getNamespace('dots');

		Assert::same('default.jpg', $namespace->getDefault());
	}


	public function testGetDefault_custom()
	{
		$namespace = $this->getManager()->getNamespace('colors');

		Assert::same('white.png', $namespace->getDefault());
	}


	public function testGetDefault_random()
	{
		$namespace = $this->getManager()->getNamespace('lines');

		Assert::contains($namespace->getDefault(), array(
			'white.png', 'black.png'
		));
	}


	public function testGetDefault_list()
	{
		$namespace = $this->getManager()->getNamespace('squares');

		Assert::contains($namespace->getDefault(), array(
			'white.png', 'black.png'
		));
	}


	public function testGetQuality()
	{
		$namespace = $this->getManager()->getNamespace('dots');

		Assert::null($namespace->getQuality());
	}


	public function testGetQuality_custom()
	{
		$namespace = $this->getManager()->getNamespace('colors');

		Assert::same(100, $namespace->getQuality());
	}


	public function testGetList()
	{
		$namespace = $this->getManager()->getNamespace('colors');
		$list = $namespace->getList('best');

		$images = array_map(function(Image $image) {
			return $image->getNamespace(). '/' .$image->getName();
		}, $list);

		$namespace->getList('best');

		Assert::equal(array(
			'colors/black.jpg',
			'colors/pink.png',
		), $images);
	}


	public function testGetList_not_exists()
	{
		$namespace = $this->getManager()->getNamespace('colors');

		Assert::exception(function() use ($namespace) {
			$namespace->getList('unknown');
		}, 'DK\ImagesManager\InvalidArgumentException', 'Images list "unknown" is not registered in "colors" namespace.');
	}

}


run(new NamespaceManagerTest);