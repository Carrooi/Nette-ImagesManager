<?php

namespace Carrooi\ImagesManager\Image;

use Carrooi\ImagesManager\Helpers\Validators;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class Image
{


	/** @var string */
	private $namespace;

	/** @var string */
	private $name;


	/**
	 * @param string $namespace
	 * @param string $name
	 */
	public function __construct($namespace, $name)
	{
		Validators::validateImageFullName($name);

		$this->namespace = $namespace;
		$this->name = $name;
	}


	/**
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

}
