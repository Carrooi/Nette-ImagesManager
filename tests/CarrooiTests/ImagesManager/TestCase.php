<?php

namespace CarrooiTests\ImagesManager;

use Nette\Configurator;
use Tester\Environment;
use Tester\TestCase as BaseTestCase;
use Carrooi\ImagesManager\DI\Extension;

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
			$config->addParameters(array('appDir' => realpath(__DIR__. '/../app')));
			$config->addConfig(__DIR__. '/../app/config/config.neon');

			Extension::register($config);

			$this->context = $config->createContainer();
		}

		return $this->context;
	}

}
