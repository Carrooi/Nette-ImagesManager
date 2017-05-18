<?php

/**
 * Test: Carrooi\ImagesManager\ImagesManager\Latte\Macros
 *
 * @testCase CarrooiTests\ImagesManager\Latte\MacrosTest
 */

namespace CarrooiTests\ImagesManager\Latte;

use Carrooi\ImagesManager\Configuration;
use Carrooi\ImagesManager\Storages\IStorage;
use CarrooiTests\TestCase;
use Nette\DI\Container;
use Tester\Assert;

require_once __DIR__. '/../../bootstrap.php';

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class MacrosTest extends TestCase
{


	public function testImage()
	{
		$template = $this->renderTemplate("{image user, '5.jpg', '20x40', fit}");

		Assert::same('http://localhost/user/5_w20_h40_f0.jpg?v=1', $template);
	}


	public function testImage_dummyImage()
	{
		$template = $this->renderTemplate("{image user, '5.jpg', '20x40', fit}", function(Container $container) {
			/** @var \CarrooiTests\Mocks\StorageMock $storage */
			$storage = $container->getByType(IStorage::class);

			/** @var \Carrooi\ImagesManager\Configuration $config */
			$config = $container->getByType(Configuration::class);

			$storage->exists = false;
			$config->enableDummy('user');
		});

		Assert::same('http://dummy-image.com/20/40/', $template);
	}


	public function testImage_notExists()
	{
		$template = $this->renderTemplate("{image user, '5.jpg', '20x40', fit}", function(Container $container) {
			/** @var \CarrooiTests\Mocks\StorageMock $storage */
			$storage = $container->getByType(IStorage::class);

			$storage->exists = false;
		});

		Assert::same('', $template);
	}


	/*public function testImage_preset_notExists()
	{
		Assert::error(function() {
			$this->renderTemplate("{image user, '5.jpg', small}");
		}, E_USER_ERROR, 'Preset small does not exists.');
	}*/


	public function testImage_preset()
	{
		$config = <<<NEON
images:
	namespaces:
		user:
			presets:
				small: 30x40(exact)
NEON;

		$template = $this->renderTemplate("{image user, '5.jpg', small}", null, $config);

		Assert::same('http://localhost/user/5_w30_h40_f8.jpg?v=1', $template);
	}


	public function testImage_preset_onlyWidth()
	{
		$config = <<<NEON
images:
	namespaces:
		user:
			presets:
				small: '30'(exact)
NEON;

		$template = $this->renderTemplate("{image user, '5.jpg', small}", null, $config);

		Assert::same('http://localhost/user/5_w30_f8.jpg?v=1', $template);
	}


	public function testSrc()
	{
		$template = $this->renderTemplate('<img n:src="user, \'5.jpg\', \'20x40\', fit">');

		Assert::same('<img src="http://localhost/user/5_w20_h40_f0.jpg?v=1">', $template);
	}


	public function testSrc_dummyImage()
	{
		$template = $this->renderTemplate('<img n:src="user, \'5.jpg\', \'20x40\', fit">', function(Container $container) {
			/** @var \CarrooiTests\Mocks\StorageMock $storage */
			$storage = $container->getByType(IStorage::class);

			/** @var \Carrooi\ImagesManager\Configuration $config */
			$config = $container->getByType(Configuration::class);

			$storage->exists = false;
			$config->enableDummy('user');
		});

		Assert::same('<img src="http://dummy-image.com/20/40/">', $template);
	}


	public function testSrc_notExists()
	{
		$template = $this->renderTemplate('<img n:src="user, \'5.jpg\', \'20x40\', fit">', function(Container $container) {
			/** @var \CarrooiTests\Mocks\StorageMock $storage */
			$storage = $container->getByType(IStorage::class);

			$storage->exists = false;
		});

		Assert::same('<img src="">', $template);
	}


	public function testIsImage()
	{
		$template = $this->renderTemplate('{isImage user, "5.jpg"}1{/isImage}');

		Assert::same('1', $template);
	}


	public function testIsImage_notExists()
	{
		$template = $this->renderTemplate('{isImage user, "5.jpg"}1{/isImage}', function(Container $container) {
			/** @var \CarrooiTests\Mocks\StorageMock $storage */
			$storage = $container->getByType(IStorage::class);

			$storage->exists = false;
		});

		Assert::same('', $template);
	}


	public function testIsNotImage()
	{
		$template = $this->renderTemplate('{isNotImage user, "5.jpg"}1{/isNotImage}', function(Container $container) {
			/** @var \CarrooiTests\Mocks\StorageMock $storage */
			$storage = $container->getByType(IStorage::class);

			$storage->exists = false;
		});

		Assert::same('1', $template);
	}


	public function testIsNotImage_exists()
	{
		$template = $this->renderTemplate('{isNotImage user, "5.jpg"}1{/isNotImage}');

		Assert::same('', $template);
	}

}


(new MacrosTest)->run();
