<?php

namespace DK\ImagesManager;

/**
 *
 * @author David Kudera
 */
interface INameResolver
{


	/**
	 * @param mixed $name
	 * @return string
	 */
	public function translateName($name);


	/**
	 * @param mixed $name
	 * @return string
	 */
	public function getDefaultName($name);

}
