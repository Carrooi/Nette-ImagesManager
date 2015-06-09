<?php

namespace DKTests\ImagesManager;

use Nette\Configurator;
use Tester\Environment;
use Tester\TestCase as BaseTestCase;
use DK\ImagesManager\DI\Extension;

/**
 *
 * @author David Kudera
 */
class TestCase extends BaseTestCase
{


	/** @var \Nette\DI\Container */
	protected $context;


	public function tearDown()
	{
		$this->context = null;
	}


	protected function lock()
	{
		Environment::lock('images-manager', __DIR__. '/../../tmp');
	}


	/**
	 * @return \Nette\DI\Container
	 */
	protected function createContainer()
	{
		if (!$this->context) {
			$config = new Configurator;

			$config->setTempDirectory(TEMP_DIR);

			$config->addParameters(array(
				'appDir' => realpath(__DIR__. '/../app'),
			));

			$config->addConfig(__DIR__. '/../app/config/config.neon');
			$config->addConfig(__DIR__. '/../app/config/images.neon');

			Extension::register($config);

			$this->context = $config->createContainer();
		}

		return $this->context;
	}


	/**
	 * @return \DK\ImagesManager\ImagesManager
	 */
	protected function getManager()
	{
		$context = $this->createContainer();
		return $context->getByType('DK\ImagesManager\ImagesManager');
	}

}
