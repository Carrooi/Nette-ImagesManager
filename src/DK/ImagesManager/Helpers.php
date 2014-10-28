<?php

namespace DK\ImagesManager;

use Nette\Object;
use Nette\Utils\Strings;
use Nette\Utils\Image as NetteImage;

/**
 *
 * @author David Kudera
 */
class Helpers extends Object
{


	/**
	 * @param string $path
	 * @return string
	 */
	public static function getExtension($path)
	{
		return strtolower(pathinfo($path, PATHINFO_EXTENSION));
	}


	/**
	 * @param string $name
	 * @return object
	 * @throws \DK\ImagesManager\InvalidArgumentException
	 */
	public static function parseName($name)
	{
		if (!is_string($name)) {
			throw new InvalidArgumentException('Name must be a string, '. gettype($name). ' given.');
		}

		$shortName = pathinfo($name, PATHINFO_FILENAME);
		$extension = pathinfo($name, PATHINFO_EXTENSION);

		if ($extension === '') {
			throw new InvalidArgumentException('Name must in "<name>.<extension>" format, "'. $name. '" given.');
		}

		if (!Strings::match($shortName, '/^'. Image::NAME_REGEX. '$/')) {
			throw new InvalidArgumentException('Name must in "<name>.<extension>" format, where <name> must be alphanumerical. "'. $name. '" given.');
		}

		return (object) array(
			'name' => $shortName,
			'extension' => $extension,
		);
	}


	/**
	 * @param int|string $size
	 * @return object
	 * @throws \DK\ImagesManager\InvalidArgumentException
	 */
	public static function parseSize($size)
	{
		if (!is_int($size) && !is_string($size)) {
			throw new InvalidArgumentException('Size must be a string or an integer, '. gettype($size). ' given.');
		}

		$width = null;
		$height = null;

		if (is_int($size)) {
			$width = (int) $size;
			$height = null;

		} elseif (($match = Strings::match($size, '/^(\d+)x(\d+)$/')) !== null) {
			$width = (int) $match[1];
			$height = (int) $match[2];

		} else {
			throw new InvalidArgumentException('Size must be in "<width>x<height>" format.');
		}

		return (object) array(
			'width' => $width,
			'height' => $height,
		);
	}


	/**
	 * @param string $fileName
	 * @param string $mask
	 * @return object
	 */
	public static function parseFileName($fileName, $mask)
	{
		$mask = strtr($mask, array(
			'<resizeFlag>' => '(?P<resizeFlag>[a-z]+)',
			'<size>' => '(?P<size>\d+|\d+x\d+)',
		));

		$match = Strings::match($fileName, '/'. $mask. '/');

		if (!$match) {
			return null;
		}

		return (object) array(
			'resizeFlag' => $match['resizeFlag'],
			'size' => mb_strpos($match['size'], 'x') === false ? (int) $match['size'] : $match['size'],
		);
	}


	/**
	 * @param string $s
	 * @param \DK\ImagesManager\Image $image
	 * @param bool $thumbnailData
	 * @return string
	 */
	public static function expand($s, Image $image, $thumbnailData = true)
	{
		$s = strtr($s, array(
			'<separator>' => DIRECTORY_SEPARATOR,
			'<namespace>' => $image->getNamespace(),
			'<name>' => $image->getName(false),
			'<extension>' => $image->getExtension(),
		));

		if ($thumbnailData) {
			$s = strtr($s, array(
				'<size>' => $image->getSize(),
				'<resizeFlag>' => $image->getResizeFlag(),
			));
		}

		return $s;
	}


	/**
	 * @param string $flag
	 * @return int
	 * @throws \DK\ImagesManager\InvalidArgumentException
	 */
	public static function getNetteResizeFlag($flag)
	{
		switch ($flag) {
			case 'fit': return NetteImage::FIT; break;
			case 'fill': return NetteImage::FILL; break;
			case 'exact': return NetteImage::EXACT; break;
			case 'shrink_only': return NetteImage::SHRINK_ONLY; break;
			case 'stretch': return NetteImage::STRETCH; break;
			default:
				throw new InvalidArgumentException('Unknown resize flag "'. $flag. '".');
			break;
		}
	}

}
 