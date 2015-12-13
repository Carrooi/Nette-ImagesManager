<?php

/**
 * Test: Carrooi\ImagesManager\ImagesManager\DI\ImagesManagerExtension
 *
 * @testCase CarrooiTests\ImagesManager\DI\ImagesManagerExtensionTest
 */

namespace CarrooiTests\ImagesManager\DI;

use Carrooi\ImagesManager\Configuration;
use Carrooi\ImagesManager\ConfigurationException;
use Carrooi\ImagesManager\Dummy\IDummyImageProvider;
use Carrooi\ImagesManager\Image\IImageFactory;
use Carrooi\ImagesManager\Image\Image;
use Carrooi\ImagesManager\ImagesManager;
use Carrooi\ImagesManager\Naming\INameResolver;
use Carrooi\ImagesManager\Storages\IStorage;
use CarrooiTests\TestCase;
use Nette\Utils\Image as NetteImage;
use Tester\Assert;

require_once __DIR__. '/../../bootstrap.php';

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class ImagesManagerExtensionTest extends TestCase
{


	public function testLoadConfiguration_unknownFactory()
	{
		$config = <<<NEON
images:
	factory:
		class: A\B\C
NEON;

		Assert::exception(function() use ($config) {
			$this->createContainer($config);
		}, ConfigurationException::class, 'Factory class A\B\C does not exists.');
	}


	public function testLoadConfiguration_unknownStorage()
	{
		$config = <<<NEON
images:
	storage:
		class: A\B\C
NEON;

		Assert::exception(function() use ($config) {
			$this->createContainer($config);
		}, ConfigurationException::class, 'Storage class A\B\C does not exists.');
	}


	public function testLoadConfiguration_unknownDummyProvider()
	{
		$config = <<<NEON
images:
	dummy:
		class: A\B\C
NEON;

		Assert::exception(function() use ($config) {
			$this->createContainer($config);
		}, ConfigurationException::class, 'Dummy provider class A\B\C for namespace default does not exists.');
	}


	public function testLoadConfiguration_unknownDummyProvider_namespace()
	{
		$config = <<<NEON
images:
	namespaces:
		user:
			dummy:
				class: A\B\C
NEON;

		Assert::exception(function() use ($config) {
			$this->createContainer($config);
		}, ConfigurationException::class, 'Dummy provider class A\B\C for namespace user does not exists.');
	}


	public function testLoadConfiguration_unknownNameResolver()
	{
		$config = <<<NEON
images:
	nameResolver:
		class: A\B\C
NEON;

		Assert::exception(function() use ($config) {
			$this->createContainer($config);
		}, ConfigurationException::class, 'Name resolver A\B\C for namespace default does not exists.');
	}


	public function testLoadConfiguration_unknownNameResolver_namespace()
	{
		$config = <<<NEON
images:
	namespaces:
		user:
			nameResolver:
				class: A\B\C
NEON;

		Assert::exception(function() use ($config) {
			$this->createContainer($config);
		}, ConfigurationException::class, 'Name resolver A\B\C for namespace user does not exists.');
	}


	public function testLoadConfiguration_unknownCache()
	{
		$config = <<<NEON
images:
	cache:
		class: A\B\C
NEON;

		Assert::exception(function() use ($config) {
			$this->createContainer($config);
		}, ConfigurationException::class, 'Cache class A\B\C does not exists.');
	}


	public function testLoadConfiguration()
	{
		$config = <<<NEON
images:
	factory:
		class: CarrooiTests\ImagesManager\DI\MockFactory

	storage:
		class: CarrooiTests\ImagesManager\DI\MockStorage
		path: /var/www

	nameResolver:
		class: CarrooiTests\ImagesManager\DI\MockNameResolver

	dummy:
		class: CarrooiTests\ImagesManager\DI\MockDummyProvider
		enabled: true
		category: cats
		fallbackSize: [800, 600]
		chance: 50

	resizeFlag: shrinkOnly|stretch
	default: default.png
	quality: 90

	namespaces:
		company-logo:
			nameResolver:
				class: CarrooiTests\ImagesManager\DI\MockNameResolverCompanyLogo

			resizeFlag: fit
			default: default.gif
			quality: 70

			dummy:
				class: CarrooiTests\ImagesManager\DI\MockDummyProviderCompanyLogo
				enabled: true
				category: dogs
				fallbackSize: [500, 400]
				chance: 90

			presets:
				small: 10x20(shrinkOnly, stretch)
				medium: 50x40(fit)
				large: 500x400

		book:
			dummy:
				enabled: false

NEON;

		$container = $this->createContainer($config);

		/** @var \Carrooi\ImagesManager\Image\IImageFactory $factory */
		$factory = $container->getByType(IImageFactory::class);

		/** @var \Carrooi\ImagesManager\Storages\IStorage $storage */
		$storage = $container->getByType(IStorage::class);

		/** @var \Carrooi\ImagesManager\Dummy\IDummyImageProvider $dummyProvider */
		$dummyProvider = $container->getService('images.dummy.default');

		/** @var \Carrooi\ImagesManager\Dummy\IDummyImageProvider $dummyProvider */
		$dummyProviderCompanyLogo = $container->getService('images.dummy.company_logo');

		/** @var \Carrooi\ImagesManager\Naming\INameResolver $nameResolverDefault */
		$nameResolverDefault = $container->getService('images.nameResolver.default');

		/** @var \Carrooi\ImagesManager\Naming\INameResolver $nameResolverDefault */
		$companyLogoResolverDefault = $container->getService('images.nameResolver.company_logo');

		/** @var \Carrooi\ImagesManager\ImagesManager $manager */
		$manager = $container->getByType(ImagesManager::class);

		Assert::type(MockFactory::class, $factory);
		Assert::type(MockStorage::class, $storage);
		Assert::type(MockDummyProvider::class, $dummyProvider);
		Assert::type(MockDummyProviderCompanyLogo::class, $dummyProviderCompanyLogo);
		Assert::type(MockNameResolver::class, $nameResolverDefault);
		Assert::type(MockNameResolverCompanyLogo::class, $companyLogoResolverDefault);
		Assert::type(ImagesManager::class, $manager);

		/** @var \Carrooi\ImagesManager\Configuration $configuration */
		$configuration = $container->getByType(Configuration::class);

		Assert::same('default.png', $configuration->getDefaultImage('color'));
		Assert::same('default.png', $configuration->getDefaultImage('book'));
		Assert::same('default.gif', $configuration->getDefaultImage('company-logo'));

		Assert::same(NetteImage::SHRINK_ONLY | NetteImage::STRETCH, $configuration->getResizeFlag('color'));
		Assert::same(NetteImage::SHRINK_ONLY | NetteImage::STRETCH, $configuration->getResizeFlag('book'));
		Assert::same(NetteImage::FIT, $configuration->getResizeFlag('company-logo'));

		Assert::same(90, $configuration->getQuality('color'));
		Assert::same(90, $configuration->getQuality('book'));
		Assert::same(70, $configuration->getQuality('company-logo'));

		Assert::type(MockNameResolver::class, $configuration->getNameResolver('color'));
		Assert::type(MockNameResolver::class, $configuration->getNameResolver('book'));
		Assert::type(MockNameResolverCompanyLogo::class, $configuration->getNameResolver('company-logo'));

		Assert::true($configuration->isDummyEnabled('color'));
		Assert::false($configuration->isDummyEnabled('book'));
		Assert::true($configuration->isDummyEnabled('company-logo'));

		Assert::type(MockDummyProvider::class, $configuration->getDummyProvider('color'));
		Assert::type(MockDummyProvider::class, $configuration->getDummyProvider('book'));
		Assert::type(MockDummyProviderCompanyLogo::class, $configuration->getDummyProvider('company-logo'));

		Assert::same('cats', $configuration->getDummyCategory('color'));
		Assert::same('cats', $configuration->getDummyCategory('book'));
		Assert::same('dogs', $configuration->getDummyCategory('company-logo'));

		Assert::equal([800, 600], $configuration->getDummyFallbackSize('color'));
		Assert::equal([800, 600], $configuration->getDummyFallbackSize('book'));
		Assert::equal([500, 400], $configuration->getDummyFallbackSize('company-logo'));

		Assert::same(50, $configuration->getDummyDisplayChance('color'));
		Assert::same(50, $configuration->getDummyDisplayChance('book'));
		Assert::same(90, $configuration->getDummyDisplayChance('company-logo'));

		Assert::true($configuration->hasPreset('company-logo', 'small'));
		Assert::true($configuration->hasPreset('company-logo', 'medium'));
		Assert::true($configuration->hasPreset('company-logo', 'large'));
		Assert::false($configuration->hasPreset('company-logo', 'extra-large'));

		Assert::equal([
			'width' => 10,
			'height' => 20,
			'resizeFlag' => NetteImage::SHRINK_ONLY | NetteImage::STRETCH,
		], $configuration->getPreset('company-logo', 'small'));

		Assert::equal([
			'width' => 50,
			'height' => 40,
			'resizeFlag' => NetteImage::FIT,
		], $configuration->getPreset('company-logo', 'medium'));

		Assert::equal([
			'width' => 500,
			'height' => 400,
			'resizeFlag' => null,
		], $configuration->getPreset('company-logo', 'large'));
	}

}


/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class MockFactory implements IImageFactory
{

	public function create($namespace, $name) {}

}


/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class MockNameResolver implements INameResolver
{

	public function getName($imageName) {}

	public function getDefaultName($imageName) {}
}


/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class MockDummyProvider implements IDummyImageProvider
{

	public function getUrl($width, $height, $category = null) {}

}


/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class MockDummyProviderCompanyLogo implements IDummyImageProvider
{

	public function getUrl($width, $height, $category = null) {}

}


/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class MockNameResolverCompanyLogo implements INameResolver
{

	public function getName($imageName) {}

	public function getDefaultName($imageName) {}
}


/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class MockStorage implements IStorage
{


	public $config;


	public function __construct(array $config)
	{
		$this->config = $config;
	}


	public function isImageExists(Image $image, $width = null, $height = null, $resizeFlag = null) {}

	public function saveImage(NetteImage $image, Image $img, $quality = null) {}

	public function removeImage(Image $image) {}

	public function getFullName($namespace, $name) {}

	public function getImageUrl(Image $image, $width = null, $height = null, $resizeFlag = null) {}

	public function getNetteImage(Image $image, $width = null, $height = null, $resizeFlag = null) {}

	public function tryStoreThumbnail(Image $image, NetteImage $thumbnail, $width = null, $height = null, $resizeFlag = null, $quality = null) {}

}


run(new ImagesManagerExtensionTest);