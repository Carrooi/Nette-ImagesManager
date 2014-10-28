<?php

namespace DK\ImagesManager\DI;

use Nette\DI\CompilerExtension;
use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\Statement;
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
		'nameResolver' => 'DK\ImagesManager\DefaultNameResolver',
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
				new Statement($config['nameResolver']),
				$config['basePath'],
				$config['baseUrl'],
				$config['mask']['images'],
				$config['mask']['thumbnails'],
				$config['resizeFlag'],
				$config['default'],
				$config['quality'],
			));

		foreach ($config['namespaces'] as $name => $definition) {
			if (!isset($definition['default'])) {
				$definition['default'] = $config['default'];
			}

			if (is_string($definition['default']) && ($match = Strings::match($definition['default'], '/^<list\|([a-zA-Z0-9]+)>$/'))) {
				$listName = $match[1];
				if (!isset($definition['lists'][$listName])) {
					throw new InvalidStateException('List "'. $listName. '" is not registered in "'. $name. '" namespace.');
				}

				$definition['default'] = $definition['lists'][$listName];
			}

			$nameResolver = isset($definition['nameResolver']) ? $definition['nameResolver'] : $config['nameResolver'];

			$namespace = $builder->addDefinition($this->prefix("namespace.$name"))
				->setClass('DK\ImagesManager\NamespaceManager', array($name, new Statement($nameResolver)))
				->setAutowired(false)
				->addSetup('setDefault', array($definition['default']))
				->addSetup('setResizeFlag', array(isset($definition['resizeFlag']) ? $definition['resizeFlag'] : $config['resizeFlag']))
				->addSetup('setQuality', array(isset($definition['quality']) ? $definition['quality'] : $config['quality']));

			if (isset($definition['lists'])) {
				foreach ($definition['lists'] as $listName => $images){
					$namespace->addSetup('addList', array($listName, $images));
				}
			}

			$manager->addSetup('addNamespace', array($name, $this->prefix("@namespace.$name")));
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
 