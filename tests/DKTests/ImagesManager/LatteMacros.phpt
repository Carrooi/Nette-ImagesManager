<?php

/**
 * Test: DK\ImagesManager\Latte\Macros
 *
 * @testCase DKTests\ImagesManager\Latte\Macros
 * @author David Kudera
 */

namespace DKTests\ImagesManager;

require_once __DIR__. '/../bootstrap.php';

use DK\ImagesManager\DefaultNameResolver;
use DK\ImagesManager\Image;
use DK\ImagesManager\ImagesManager;
use Nette\Templating\FileTemplate;
use Nette\Latte\Engine;
use Tester\Assert;
use DKTests\Mocks\Control;
use DK\ImagesManager\Latte\Macros;
use Tester\FileMock;

/**
 *
 * @author David Kudera
 */
class LatteMacrosTest extends TestCase
{


	/**
	 * @param string $tmpl
	 * @return string
	 */
	private function renderTemplate($tmpl)
	{
		$container = $this->createContainer();

		if (class_exists('Nette\Bridges\ApplicationLatte\TemplateFactory')) {
			$factory = $container->getByType('Nette\Bridges\ApplicationLatte\TemplateFactory');		/** @var $factory \Nette\Bridges\ApplicationLatte\TemplateFactory */
			$template = $factory->createTemplate(new Control);

		} else {
			$manager = new ImagesManager(new DefaultNameResolver, '', '');
			$template = new FileTemplate;
			$engine = new Engine;

			$template->registerHelperLoader(array($manager->createTemplateHelpers(), 'loader'));
			$template->registerFilter($engine);

			Macros::install($engine->getCompiler());
		}

		return trim($template->setFile(FileMock::create($tmpl, 'latte')));
	}


	public function testSrcMacro()
	{
		$template = $this->renderTemplate('<img n:src="dots, \'black.png\'">');

		Assert::same('<img src="/images/dots/black.png">', $template);
	}


	public function testSrcMacro_absolute()
	{
		$template = $this->renderTemplate('<img n:src="dots, \'//black.png\'">');

		Assert::same('<img src="http://localhost/images/dots/black.png">', $template);
	}


	public function testSrcMacro_default()
	{
		$template = $this->renderTemplate('<img n:src="dots, \'pink.png\'">');

		Assert::same('<img src="/images/dots/default.jpg">', $template);
	}


	public function testSrcMacro_without_default()
	{
		$template = $this->renderTemplate('<img n:src="blackness, \'green.png\'">');

		Assert::same('<img src="">', $template);
	}


	public function testSrcMacro_thumbnail()
	{
		$template = $this->renderTemplate('<img n:src="dots, \'black.png\', \'20x55\', stretch">');

		Assert::same('<img src="/images/dots/black_stretch_20x55.png">', $template);
	}


	public function testSrcMacro_thumbnail_create()
	{
		$this->lock();

		$image = new Image('dots', 'black.png');
		$image->setBasePath(__DIR__. '/../www/images');
		$image->setSize('2x3');

		Assert::false($image->isExists());

		Assert::same('<img src="/images/dots/black_fit_2x3.png">', $this->renderTemplate('<img n:src="dots, \'black.png\', \'2x3\'">'));

		Assert::true($image->isExists());

		unlink($image->getPath());
	}


	public function testImageMacro()
	{
		$template = $this->renderTemplate('{image dots, \'black.jpg\'}');

		Assert::same('/images/dots/black.jpg', $template);
	}


	public function testIsImageMacro()
	{
		$template = $this->renderTemplate('{isImage dots, \'black.png\'}exists{/isImage}');

		Assert::same('exists', $template);
	}


	public function testIsImage_not()
	{
		$template = $this->renderTemplate('{isImage dots, \'pink.png\'}exists{/isImage}');

		Assert::same('', $template);
	}


	public function testIsImageMacro_attr()
	{
		$template = $this->renderTemplate('<img n:isImage="dots, \'black.png\'" n:src="dots, \'black.png\'">');

		Assert::same('<img src="/images/dots/black.png">', $template);
	}


	public function testIsNotImage()
	{
		$template = $this->renderTemplate('{isNotImage dots, \'pink.png\'}not exists{/isNotImage}');

		Assert::same('not exists', $template);
	}

}


run(new LatteMacrosTest);
