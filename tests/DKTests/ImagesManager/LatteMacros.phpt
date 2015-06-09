<?php

/**
 * Test: DK\ImagesManager\Latte\Macros
 *
 * @testCase DKTests\ImagesManager\Latte\Macros
 * @author David Kudera
 */

namespace DKTests\ImagesManager;

require_once __DIR__. '/../bootstrap.php';

use DK\ImagesManager\Image;
use DK\ImagesManager\ImagesManager;
use Nette\Templating\FileTemplate;
use Nette\Latte\Engine;
use Tester\Assert;
use DKTests\Mocks\Control;
use DK\ImagesManager\Latte\Macros;

/**
 *
 * @author David Kudera
 */
class LatteMacrosTest extends TestCase
{


	/**
	 * @param string $path
	 * @param callable $onBeforeRender
	 * @return string
	 */
	private function renderTemplate($path, $onBeforeRender = null)
	{
		$container = $this->createContainer();
		$manager = $container->getByType('DK\ImagesManager\ImagesManager');

		if (class_exists('Nette\Bridges\ApplicationLatte\TemplateFactory')) {
			$factory = $container->getByType('Nette\Application\UI\ITemplateFactory');		/** @var $factory \Nette\Application\UI\ITemplateFactory */
			$template = $factory->createTemplate(new Control);
		} else {
			$template = new FileTemplate;
			$engine = new Engine;

			$template->registerHelperLoader(array($manager->createTemplateHelpers(), 'loader'));
			$template->registerFilter($engine);

			Macros::install($engine->getCompiler());
		}

		if ($onBeforeRender) {
			$onBeforeRender($manager);
		}

		return trim($template->setFile($path));
	}


	public function testSrcMacro()
	{
		$template = $this->renderTemplate(__DIR__. '/templates/src.latte');

		Assert::same('<img src="/images/dots/black.png">', $template);
	}


	public function testSrcMacro_absolute()
	{
		$template = $this->renderTemplate(__DIR__. '/templates/src.absolute.latte', function(ImagesManager $manager) {
			$manager->setHost('http://localhost');
		});

		Assert::same('<img src="http://localhost/images/dots/black.png">', $template);
	}


	public function testSrcMacro_default()
	{
		$template = $this->renderTemplate(__DIR__. '/templates/src.defaultImage.latte');

		Assert::same('<img src="/images/dots/default.jpg">', $template);
	}


	public function testSrcMacro_without_default()
	{
		$template = $this->renderTemplate(__DIR__. '/templates/src.withoutDefaultImage.latte', function(ImagesManager $manager) {
			$manager->setDefault(null);
		});

		Assert::same('<img src="">', $template);
	}


	public function testSrcMacro_thumbnail()
	{
		$template = $this->renderTemplate(__DIR__. '/templates/src.thumbnail.latte');

		Assert::same('<img src="/images/dots/black_stretch_20x55.png">', $template);
	}


	public function testSrcMacro_thumbnail_withoutDefault()
	{
		$template = $this->renderTemplate(__DIR__. '/templates/src.thumbnail.withoutDefaultImage.latte', function(ImagesManager $manager) {
			$manager->setDefault(null);
		});

		Assert::same('<img src="http://satyr.io/20x20">', $template);
	}


	public function testSrcMacro_thumbnail_create()
	{
		$this->lock();

		$image = new Image('dots', 'black.png');
		$image->setBasePath(__DIR__. '/../www/images');
		$image->setSize('2x3');

		Assert::false($image->isExists());

		$template = $this->renderTemplate(__DIR__. '/templates/src.thumbnail.create.latte');

		Assert::same('<img src="/images/dots/black_fit_2x3.png">', $template);

		Assert::true($image->isExists());

		unlink($image->getPath());
	}


	public function testImageMacro()
	{
		$template = $this->renderTemplate(__DIR__. '/templates/image.latte');

		Assert::same('/images/dots/black.jpg', $template);
	}


	public function testIsImageMacro()
	{
		$template = $this->renderTemplate(__DIR__. '/templates/isImage.latte');

		Assert::same('exists', $template);
	}


	public function testIsImage_not()
	{
		$template = $this->renderTemplate(__DIR__. '/templates/isImage.not.latte');

		Assert::same('', $template);
	}


	public function testIsImageMacro_attr()
	{
		$template = $this->renderTemplate(__DIR__. '/templates/isImage.attr.latte');

		Assert::same('<img src="/images/dots/black.png">', $template);
	}


	public function testIsNotImage()
	{
		$template = $this->renderTemplate(__DIR__. '/templates/isNotImage.latte');

		Assert::same('not exists', $template);
	}

}


run(new LatteMacrosTest);
