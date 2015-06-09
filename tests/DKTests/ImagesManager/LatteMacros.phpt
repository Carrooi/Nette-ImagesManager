<?php

/**
 * Test: DK\ImagesManager\Latte\Macros
 *
 * @testCase DKTests\ImagesManager\Latte\Macros
 * @author David Kudera
 */

namespace DKTests\ImagesManager;

require_once __DIR__. '/../bootstrap.php';

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
	 * @param string $name
	 * @return string
	 */
	private function renderTemplate($name)
	{
		$container = $this->createContainer();

		if (class_exists('Nette\Bridges\ApplicationLatte\TemplateFactory')) {
			$factory = $container->getByType('Nette\Bridges\ApplicationLatte\TemplateFactory');		/** @var $factory \Nette\Bridges\ApplicationLatte\TemplateFactory */
			$template = $factory->createTemplate(new Control);

		} else {
			$manager = $this->getManager();
			$template = new FileTemplate;
			$engine = new Engine;

			$template->registerHelperLoader(array($manager->createTemplateHelpers(), 'loader'));
			$template->registerFilter($engine);

			Macros::install($engine->getCompiler());
		}


		return (string) $template->setFile(__DIR__. '/templates/'. $name. '.latte');
	}


	public function testSrcMacro()
	{
		$template = $this->renderTemplate('macro.src');

		Assert::same('<img src="/images/base/dots/dots_black.png">', $template);
	}


	public function testSrcMacro_absolute()
	{
		$template = $this->renderTemplate('macro.src.absolute');

		Assert::same('<img src="http://localhost/images/base/dots/dots_black.png">', $template);
	}


	public function testSrcMacro_default()
	{
		$template = $this->renderTemplate('macro.src.default');

		Assert::same('<img src="/images/base/dots/dots_default.jpg">', $template);
	}


	public function testSrcMacro_without_default()
	{
		$template = $this->renderTemplate('macro.src.without_default');

		Assert::same('<img src="">', $template);
	}


	public function testSrcMacro_thumbnail()
	{
		$template = $this->renderTemplate('macro.src.thumbnail');

		Assert::same('<img src="/images/base/dots/dots_black_stretch_20x55.png">', $template);
	}


	public function testSrcMacro_thumbnail_create()
	{
		$this->lock();

		$manager = $this->getManager();
		$image = $manager->load('dots', 'black.png')->setSize('2x3');

		Assert::false($image->isExists());

		Assert::same('<img src="/images/base/dots/dots_black_fit_2x3.png">', $this->renderTemplate('macro.src.thumbnail.create'));

		Assert::true($image->isExists());

		unlink($image->getPath());
	}


	public function testImageMacro()
	{
		$template = $this->renderTemplate('macro.image');

		Assert::same('/images/base/dots/dots_black.jpg', $template);
	}


	public function testIsImageMacro()
	{
		$template = $this->renderTemplate('macro.isImage');

		Assert::same('exists', $template);
	}


	public function testIsImage_not()
	{
		$template = $this->renderTemplate('macro.isImage.not');

		Assert::same('', $template);
	}


	public function testIsImageMacro_attr()
	{
		$template = $this->renderTemplate('macro.isImage.attr');

		Assert::same('<img src="/images/base/dots/dots_black.png">', $template);
	}


	public function testIsNotImage()
	{
		$template = $this->renderTemplate('macro.isNotImage');

		Assert::same('not exists', $template);
	}

}


run(new LatteMacrosTest);