<?php

namespace DKTests\ImagesManager;

use Tester\TestCase as BaseTestCase;
use Nette\Configurator;
use DK\ImagesManager\DI\Extension;

/**
 *
 * @author David Kudera
 */
class TestCase extends BaseTestCase
{


	/** @var \Nette\DI\Container */
	protected $context;


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
 