<?php

namespace DK\ImagesManager\DI;

use Nette\DI\CompilerExtension;
use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\Utils\Strings;
use DK\ImagesManager\InvalidStateException;

if (!class_exists('Latte\Compiler')) {
	class_alias('Nette\Latte\Macros\MacroSet', 'Latte\Macros\MacroSet');
	class_alias('Nette\Latte\Compiler', 'Latte\Compiler');
	class_alias('Nette\Latte\MacroNode', 'Latte\MacroNode');
	class_alias('Nette\Latte\PhpWriter', 'Latte\PhpWriter');
	class_alias('Nette\Image', 'Nette\Utils\Image');
}

/**
 *
 * @author David Kudera
 */
class Extension extends CompilerExtension
{


	/** @var array */
	private $defaults = array(
		'resizeFlag' => 'fit',
		'default' => 'default.jpg',
		'quality' => null,
		'basePath' => null,
		'baseUrl' => null,
		'mask' => array(
			'images' => '<namespace><separator><name>.<extension>',
			'thumbnails' => '<namespace><separator><name>_<resizeFlag>_<size>.<extension>',
		),
		'namespaces' => array(),
	);


	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		$manager = $builder->addDefinition($this->prefix('manager'))
			->setClass('DK\ImagesManager\ImagesManager', array(
				$config['basePath'],
				$config['baseUrl'],
				$config['mask']['images'],
				$config['mask']['thumbnails'],
				$config['resizeFlag'],
				$config['default'],
				$config['quality'],
			));

		foreach ($config['namespaces'] as $namespace => $definition) {
			$manager->addSetup('setNamespaceDefinition', array($namespace, $this->parseNamespaceDefinition($config, $namespace, $definition)));
		}

		$builder->addDefinition($this->prefix('helpers'))
			->setClass('DK\ImagesManager\Latte\Helpers')
			->setFactory($this->prefix('@manager'). '::createTemplateHelpers')
			->setInject(false);

		$latteFactory = $builder->hasDefinition('nette.latteFactory')
			? $builder->getDefinition('nette.latteFactory')
			: $builder->getDefinition('nette.latte');

		$latteFactory
			->addSetup('DK\ImagesManager\Latte\Macros::install(?->getCompiler())', array('@self'))
			->addSetup('addFilter', array('getImagesManager', array($this->prefix('@helpers'), 'getImagesManager')));
	}


	/**
	 * @param array $config
	 * @param string $namespace
	 * @param array $definition
	 * @return array
	 * @throws \DK\ImagesManager\InvalidStateException
	 */
	private function parseNamespaceDefinition(array $config, $namespace, array $definition)
	{
		if (!array_key_exists('lists', $definition)) {
			$definition['lists'] = array();
		}

		$definition['lists'] = array_map(function($images) {
			return array(
				'images' => $images,
				'parsed' => null,
			);
		}, $definition['lists']);

		if (!array_key_exists('resizeFlag', $definition)) {
			$definition['resizeFlag'] = $config['resizeFlag'];
		}

		if (!array_key_exists('default', $definition)) {
			$definition['default'] = $config['default'];
		}

		if (is_string($definition['default']) && ($match = Strings::match($definition['default'], '/^<list\|([a-zA-Z0-9]+)>$/'))) {
			$default = $match[1];
			if (!isset($definition['lists'][$default])) {
				throw new InvalidStateException('List "'. $default. '" is not registered in "'. $namespace. '" namespace.');
			}

			$definition['default'] = $definition['lists'][$default]['images'];
		}

		if (!array_key_exists('quality', $definition)) {
			$definition['quality'] = $config['quality'];
		}

		return $definition;
	}


	/**
	 * @param \Nette\Configurator $configurator
	 * @param string $name
	 */
	public static function register(Configurator $configurator, $name = 'images')
	{
		$configurator->onCompile[] = function($config, Compiler $compiler) use ($name) {
			$compiler->addExtension($name, new Extension);
		};
	}

}
 