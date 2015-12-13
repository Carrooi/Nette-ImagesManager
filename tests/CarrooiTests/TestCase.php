<?php

namespace CarrooiTests;

use CarrooiTests\Mocks\ControlMock;
use Nette\Application\UI\ITemplateFactory;
use Nette\Configurator;
use Tester\FileMock;
use Tester\TestCase as BaseTestCase;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class TestCase extends BaseTestCase
{


	/** @var \Nette\DI\Container */
	protected $context;


	public function tearDown()
	{
		$this->context = null;
	}


	/**
	 * @param string|null $customConfig
	 * @return \Nette\DI\Container
	 */
	protected function createContainer($customConfig = null)
	{
		if (!$this->context) {
			$config = new Configurator;

			$config->setTempDirectory(TEMP_DIR);
			$config->addParameters([
				'appDir' => __DIR__. '/app',
			]);

			$config->addConfig(__DIR__. '/app/config/config.neon');

			if ($customConfig) {
				$config->addConfig(FileMock::create($customConfig, 'neon'));
			}

			$this->context = $config->createContainer();
		}

		return $this->context;
	}


	/**
	 * @param string $code
	 * @param callable|null $onContainer
	 * @param string|null $customConfig
	 * @return string
	 */
	protected function renderTemplate($code, callable $onContainer = null, $customConfig = null)
	{
		$container = $this->createContainer($customConfig);

		if ($onContainer) {
			$onContainer($container);
		}

		/** @var \Nette\Application\UI\ITemplateFactory $factory */
		$factory = $container->getByType(ITemplateFactory::class);
		$template = $factory->createTemplate(new ControlMock);

		$template->setFile(FileMock::create($code, 'latte'));

		return (string) $template;
	}

}
