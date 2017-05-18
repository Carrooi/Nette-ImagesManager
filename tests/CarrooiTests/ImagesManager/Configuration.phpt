<?php

/**
 * Test: Carrooi\ImagesManager\ImagesManager\Configuration
 *
 * @testCase CarrooiTests\ImagesManager\ConfigurationTest
 */

namespace CarrooiTests\ImagesManager;

use Carrooi\ImagesManager\Configuration;
use Carrooi\ImagesManager\Dummy\IDummyImageProvider;
use Carrooi\ImagesManager\Dummy\LorempixelDummyImageProvider;
use Carrooi\ImagesManager\Naming\DefaultNameResolver;
use Carrooi\ImagesManager\Naming\INameResolver;
use Nette\Utils\Image as NetteImage;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__. '/../bootstrap.php';

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class ConfigurationTest extends TestCase
{


	public function tearDown()
	{
		\Mockery::close();
	}


	public function testDefaultImage()
	{
		$configuration = new Configuration;

		Assert::same('default.jpg', $configuration->getDefaultImage('color'));

		$configuration->setDefaultImage('color', 'default.png');

		Assert::same('default.png', $configuration->getDefaultImage('color'));
		Assert::same('default.jpg', $configuration->getDefaultImage('user'));
	}


	public function testResizeFlag()
	{
		$configuration = new Configuration;

		Assert::same(NetteImage::FIT, $configuration->getResizeFlag('color'));

		$configuration->setResizeFlag('color', NetteImage::EXACT);

		Assert::same(NetteImage::EXACT, $configuration->getResizeFlag('color'));
		Assert::same(NetteImage::FIT, $configuration->getResizeFlag('user'));
	}


	public function testQuality()
	{
		$configuration = new Configuration;

		Assert::null($configuration->getQuality('color'));

		$configuration->setQuality('color', 90);

		Assert::same(90, $configuration->getQuality('color'));
		Assert::null($configuration->getQuality('user'));
	}


	public function testNameResolver()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Naming\INameResolver $nameResolver */
		$nameResolver = \Mockery::mock(INameResolver::class);

		$configuration = new Configuration;

		Assert::type(DefaultNameResolver::class, $configuration->getNameResolver('color'));

		$configuration->setNameResolver('color', $nameResolver);

		Assert::type(get_class($nameResolver), $configuration->getNameResolver('color'));
		Assert::type(DefaultNameResolver::class, $configuration->getNameResolver('user'));
	}


	public function testDummyEnabled()
	{
		$configuration = new Configuration;

		Assert::false($configuration->isDummyEnabled('color'));

		$configuration->enableDummy('color');

		Assert::true($configuration->isDummyEnabled('color'));
		Assert::false($configuration->isDummyEnabled('user'));

		$configuration->enableDummy('color', false);

		Assert::false($configuration->isDummyEnabled('color'));
	}


	public function testDummyProvider()
	{
		/** @var \Mockery\MockInterface|\Carrooi\ImagesManager\Dummy\IDummyImageProvider $provider */
		$provider = \Mockery::mock(IDummyImageProvider::class);

		$configuration = new Configuration;

		Assert::type(LorempixelDummyImageProvider::class, $configuration->getDummyProvider('color'));

		$configuration->setDummyProvider('color', $provider);

		Assert::type(get_class($provider), $configuration->getDummyProvider('color'));
		Assert::type(LorempixelDummyImageProvider::class, $configuration->getDummyProvider('user'));
	}


	public function testDummyCategory()
	{
		$configuration = new Configuration;

		Assert::null($configuration->getDummyCategory('color'));

		$configuration->setDummyCategory('color', 'cats');

		Assert::same('cats', $configuration->getDummyCategory('color'));
		Assert::null($configuration->getDummyCategory('user'));
	}


	public function testDummyFallbackSize()
	{
		$configuration = new Configuration;

		Assert::null($configuration->getDummyFallbackSize('color'));

		$configuration->setDummyFallbackSize('color', 800, 600);

		Assert::equal([800, 600], $configuration->getDummyFallbackSize('color'));
		Assert::null($configuration->getDummyFallbackSize('user'));
	}


	public function testDummyDisplayChance()
	{
		$configuration = new Configuration;

		Assert::same(100, $configuration->getDummyDisplayChance(Configuration::DEFAULT_NAMESPACE_NAME));

		$configuration->setDummyDisplayChance('color', 50);

		Assert::same(50, $configuration->getDummyDisplayChance('color'));
		Assert::same(100, $configuration->getDummyDisplayChance('user'));
	}

}


(new ConfigurationTest)->run();
