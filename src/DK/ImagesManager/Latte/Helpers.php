<?php

namespace DK\ImagesManager\Latte;

use Nette\Object;
use Latte\Engine;
use DK\ImagesManager\ImagesManager;

/**
 *
 * @author David Kudera
 */
class Helpers extends Object
{


	/** @var \DK\ImagesManager\ImagesManager  */
	private $imagesManager;


	/**
	 * @param \DK\ImagesManager\ImagesManager $imagesManager
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
	 * @return \DK\ImagesManager\ImagesManager
	 */
	public function getImagesManager()
	{
		return $this->imagesManager;
	}

}
 