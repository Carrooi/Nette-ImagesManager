<?php

namespace Carrooi\ImagesManager;

use Nette\Object;

/**
 *
 * @author David Kudera
 */
class ParsedName extends Object
{


	/** @var string */
	private $name;


	/**
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

}
