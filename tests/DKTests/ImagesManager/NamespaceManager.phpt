<?php

/**
 * Test: DK\ImagesManager\NamespaceManager
 *
 * @testCase DKTests\ImagesManager\NamespaceManagerTest
 * @author David Kudera
 */

namespace DKTests\ImagesManager;

require_once __DIR__. '/../bootstrap.php';

use DK\ImagesManager\DefaultNameResolver;
use DK\ImagesManager\ImagesManager;
use DK\ImagesManager\NamespaceManager;
use Tester\Assert;
use DK\ImagesManager\Image;

/**
 *
 * @author David Kudera
 */
class NamespaceManagerTest extends TestCase
{


	public function testResizeFlag()
	{
		$namespace = new NamespaceManager('dots', new DefaultNameResolver);

		Assert::same('fit', $namespace->getResizeFlag());

		$namespace->setResizeFlag('stretch');

		Assert::same('stretch', $namespace->getResizeFlag());
	}


	public function testDefault()
	{
		$namespace = new NamespaceManager('dots', new DefaultNameResolver);

		Assert::same('default.jpg', $namespace->getDefault());

		$namespace->setDefault('default.png');

		Assert::same('default.png', $namespace->getDefault());

		$namespace->setDefault(array('default.png', 'default.jpg'));

		Assert::contains($namespace->getDefault(), array(
			'default.png', 'default.jpg'
		));
	}


	public function testQuality()
	{
		$namespace = new NamespaceManager('dots', new DefaultNameResolver);

		Assert::same(90, $namespace->getQuality());

		$namespace->setQuality(100);

		Assert::same(100, $namespace->getQuality());
	}


	public function testGetList()
	{
		$manager = new ImagesManager(new DefaultNameResolver, '/var/www/images', '/');

		$namespace = new NamespaceManager('dots', new DefaultNameResolver);
		$namespace->registerImagesManager($manager);

		$namespace->addList('best', array(
			'black.jpg',
			'white.png',
		));

		$list = $namespace->getList('best');

		$images = array_map(function(Image $image) {
			return $image->getNamespace(). '/' .$image->getName();
		}, $list);

		Assert::equal(array(
			'dots/black.jpg',
			'dots/white.png',
		), $images);
	}


	public function testGetList_not_exists()
	{
		$namespace = new NamespaceManager('dots', new DefaultNameResolver);

		Assert::exception(function() use ($namespace) {
			$namespace->getList('unknown');
		}, 'DK\ImagesManager\InvalidArgumentException', 'Images list "unknown" is not registered in "dots" namespace.');
	}

}


run(new NamespaceManagerTest);
