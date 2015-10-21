<?php

namespace Carrooi\ImagesManager\DI;

use Carrooi\ImagesManager\Caching\CachedStorage;
use Carrooi\ImagesManager\Configuration;
use Carrooi\ImagesManager\ConfigurationException;
use Carrooi\ImagesManager\Helpers\Helpers;
use Carrooi\ImagesManager\Image\ImageFactory;
use Carrooi\ImagesManager\ImagesManager;
use Carrooi\ImagesManager\Latte\Helpers as LatteHelpers;
use Carrooi\ImagesManager\Latte\Macros;
use Carrooi\ImagesManager\Naming\DefaultNameResolver;
use Carrooi\ImagesManager\Storages\FileSystemStorage;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;
use Nette\Utils\Strings;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class ImagesManagerExtension extends CompilerExtension
{


	/** @var array */
	private $defaults = [
		'nameResolver' => [
			'class' => DefaultNameResolver::class,
		],
		'factory' => [
			'class' => ImageFactory::class,
		],
		'storage' => [
			'class' => FileSystemStorage::class,
		],
		'cache' => [
			'class' => CachedStorage::class,
		],
		'resizeFlag' => null,
		'default' => null,
		'quality' => null,
		'dummy' => [],
		'namespaces' => [],
	];

	/** @var array */
	private $namespaceDefaults = [
		'nameResolver' => [
			'class' => null,
		],
		'resizeFlag' => null,
		'default' => null,
		'quality' => null,
		'dummy' => [],
	];

	/** @var array */
	private $dummyDefaults = [
		'class' => null,
		'enabled' => null,
		'category' => null,
		'fallbackSize' => null,
		'chance' => null,
	];


	public function loadConfiguration()
	{
		$config = $this->validateConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$config['dummy'] = $this->validateConfig($this->dummyDefaults, $config['dummy']);

		$config['namespaces'] = array_map(function(array $namespace) {
			$namespace = $this->validateConfig($this->namespaceDefaults, $namespace);
			$namespace['dummy'] = $this->validateConfig($this->dummyDefaults, $namespace['dummy']);

			return $namespace;
		}, $config['namespaces']);

		if (!class_exists($config['storage']['class'])) {
			throw new ConfigurationException('Storage class '. $config['storage']['class']. ' does not exists.');
		}

		if (!class_exists($config['factory']['class'])) {
			throw new ConfigurationException('Factory class '. $config['factory']['class']. ' does not exists.');
		}

		if (!class_exists($config['cache']['class'])) {
			throw new ConfigurationException('Cache class '. $config['cache']['class']. ' does not exists.');
		}

		$storageClass = $config['storage']['class'];
		unset($config['storage']['class']);
		$storage = $builder->addDefinition($this->prefix('storage'))
			->setClass($storageClass);

		if (count($config['storage'])) {
			$storage->setArguments([$config['storage']]);
		}

		$configuration = $builder->addDefinition($this->prefix('configuration'))
			->setClass(Configuration::class);

		$this->applyNamespaceConfiguration($configuration, Configuration::DEFAULT_NAMESPACE_NAME, $config);

		foreach ($config['namespaces'] as $name => $namespace) {
			$this->applyNamespaceConfiguration($configuration, $name, $namespace);
		}

		$builder->addDefinition($this->prefix('factory'))
			->setClass($config['factory']['class']);

		$builder->addDefinition($this->prefix('cache'))
			->setClass($config['cache']['class']);

		$builder->addDefinition($this->prefix('manager'))
			->setClass(ImagesManager::class);

		$builder->addDefinition($this->prefix('helpers'))
			->setClass(LatteHelpers::class)
			->setFactory($this->prefix('@manager'). '::createTemplateHelpers')
			->setInject(false);

		$latteFactory = $builder->hasDefinition('nette.latteFactory') ?
			$builder->getDefinition('nette.latteFactory') :
			$builder->getDefinition('nette.latte');

		$latteFactory
			->addSetup(Macros::class. '::install(?->getCompiler())', ['@self'])
			->addSetup('addFilter', ['getImagesManager', [$this->prefix('@helpers'), 'getImagesManager']]);
	}


	/**
	 * @param \Nette\DI\ServiceDefinition $configuration
	 * @param string $namespace
	 * @param array $config
	 */
	private function applyNamespaceConfiguration(ServiceDefinition $configuration, $namespace, array $config)
	{
		$builder = $this->getContainerBuilder();
		$safeNamespace = str_replace('-', '_', Strings::webalize($namespace));

		if ($namespace === Configuration::DEFAULT_NAMESPACE_NAME || $config['nameResolver']['class']) {
			if (!class_exists($config['nameResolver']['class'])) {
				throw new ConfigurationException('Name resolver '. $config['nameResolver']['class']. ' for namespace '. $namespace. ' does not exists.');
			}

			$nameResolver = $builder->addDefinition($this->prefix('nameResolver.'. $safeNamespace))
				->setClass($config['nameResolver']['class']);

			$configuration->addSetup('setNameResolver', [$namespace, $nameResolver]);
		}

		if ($config['default'] !== null) {
			$configuration->addSetup('setDefaultImage', [$namespace, $config['default']]);
		}

		if ($config['resizeFlag'] !== null) {
			$resizeFlag = Helpers::parseResizeFlags($config['resizeFlag']);
			$configuration->addSetup('setResizeFlag', [$namespace, $resizeFlag]);
		}

		if ($config['quality'] !== null) {
			$configuration->addSetup('setQuality', [$namespace, $config['quality']]);
		}

		if ($config['dummy']['enabled'] !== null) {
			$configuration->addSetup('enableDummy', [$namespace, $config['dummy']['enabled']]);
		}

		if ($config['dummy']['class'] !== null) {
			if (!class_exists($config['dummy']['class'])) {
				throw new ConfigurationException('Dummy provider class '. $config['dummy']['class']. ' for namespace '. $namespace. ' does not exists.');
			}

			$provider = $builder->addDefinition($this->prefix('dummy.'. $safeNamespace))
				->setClass($config['dummy']['class']);

			$configuration->addSetup('setDummyProvider', [$namespace, $provider]);
		}

		if ($config['dummy']['category'] !== null) {
			$configuration->addSetup('setDummyCategory', [$namespace, $config['dummy']['category']]);
		}

		if ($config['dummy']['fallbackSize'] !== null) {
			$size = $config['dummy']['fallbackSize'];
			$configuration->addSetup('setDummyFallbackSize', [$namespace, $size[0], $size[1]]);
		}

		if ($config['dummy']['chance'] !== null) {
			$configuration->addSetup('setDummyDisplayChance', [$namespace, $config['dummy']['chance']]);
		}
	}

}
