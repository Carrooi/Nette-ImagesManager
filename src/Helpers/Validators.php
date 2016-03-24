<?php

namespace Carrooi\ImagesManager\Helpers;

use Carrooi\ImagesManager\InvalidImageNameException;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class Validators
{


	/**
	 * @param string $name
	 * @return bool
	 */
	public static function isImageFullName($name)
	{
		return preg_match('/^[a-zA-Z0-9-_]+\.jpg|jpeg|gif|png$/i', $name) ? true : false;
	}


	/**
	 * @param string $name
	 * @throws \Carrooi\ImagesManager\InvalidImageNameException
	 */
	public static function validateImageFullName($name)
	{
		if (!self::isImageFullName($name)) {
			throw new InvalidImageNameException('Image name must be with valid extension, '. $name. ' given.');
		}
	}

}
