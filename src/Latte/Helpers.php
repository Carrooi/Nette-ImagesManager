<?php

namespace Carrooi\ImagesManager\Latte;

use Nette\Object;
use Latte\Engine;
use Carrooi\ImagesManager\ImagesManager;

/**
 *
 * @author David Kudera
 */
class Helpers extends Object
{


	/** @var \Carrooi\ImagesManager\ImagesManager  */
	private $imagesManager;


	/**
	 * @param \Carrooi\ImagesManager\ImagesManager $imagesManager
	 */
	public function __construct(ImagesManager $imagesManager)
	{
		$this->imagesManager = $imagesManager;
	}


	/**
	 * @param \Latte\Engine $engine
	 */
	public function register(Engine $engine)
	{
		$engine->addFilter('getImagesManager', array($this, 'getImagesManager'));
	}


	/**
	 * @deprecated
	 * @param string $method
	 * @return array
	 */
	public function loader($method)
	{
		if (method_exists($this, $method)) {
			return array($this, $method);
		}

		return null;
	}


	/**
	 * @return \Carrooi\ImagesManager\ImagesManager
	 */
	public function getImagesManager()
	{
		return $this->imagesManager;
	}

}
