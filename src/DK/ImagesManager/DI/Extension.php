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
		'resizeFlag' => null,
		'default' => null,
		'quality' => null,
		'basePath' => null,
		'baseUrl' => null,
		'caching' => true,
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
				$config['quality'],
			))
			->addSetup('setHostFromUrl', array('@Nette\Http\Request::url'));

		if ($config['resizeFlag']) {
			$manager->addSetup('setResizeFlag', array($config['resizeFlag']));
		}

		if ($config['default']) {
			$manager->addSetup('setDefault', array($config['default']));
		}

		if ($config['caching']) {
			$manager->addSetup('setCaching', array('@Nette\Caching\IStorage'));
		}

		foreach ($config['namespaces'] as $name => $definition) {
			$nameResolver = isset($definition['nameResolver']) ? $definition['nameResolver'] : $config['nameResolver'];

			$namespace = $builder->addDefinition($this->prefix("namespace.$name"))
				->setClass('DK\ImagesManager\NamespaceManager', array($name, new Statement($nameResolver)))
				->setAutowired(false)
				->addSetup('setQuality', array(isset($definition['quality']) ? $definition['quality'] : $config['quality']));

			if (isset($definition['resizeFlag'])) {
				$namespace->addSetup('setResizeFlag', array($definition['resizeFlag']));
			}

			if (isset($definition['default'])) {
				if (is_string($definition['default']) && ($match = Strings::match($definition['default'], '/^<list\|([a-zA-Z0-9]+)>$/'))) {
					$listName = $match[1];
					if (!isset($definition['lists'][$listName])) {
						throw new InvalidStateException('List "'. $listName. '" is not registered in "'. $name. '" namespace.');
					}

					$definition['default'] = $definition['lists'][$listName];
				}

				$namespace->addSetup('setDefault', array($definition['default']));
			}

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
 