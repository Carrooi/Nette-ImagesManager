<?php

namespace Carrooi\ImagesManager\Latte;

use Carrooi\ImagesManager\ImagesManager;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class Helpers
{


	/** @var \Carrooi\ImagesManager\ImagesManager */
	private $imagesManager;


	/**
	 * @param \Carrooi\ImagesManager\ImagesManager $imagesManager
	 */
	public function __construct(ImagesManager $imagesManager)
	{
		$this->imagesManager = $imagesManager;
	}


	/**
	 * @return \Carrooi\ImagesManager\ImagesManager
	 */
	public function getImagesManager()
	{
		return $this->imagesManager;
	}

}
