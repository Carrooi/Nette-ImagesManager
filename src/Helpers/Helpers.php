<?php

namespace Carrooi\ImagesManager\Helpers;

use Carrooi\ImagesManager\InvalidArgumentException;
use Nette\Utils\Image as NetteImage;
use Nette\Utils\Strings;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class Helpers
{


	/**
	 * @param string $flags
	 * @return int
	 * @throws \Carrooi\ImagesManager\InvalidArgumentException
	 */
	public static function parseResizeFlags($flags)
	{
		$flags = explode('|', $flags);

		$flags = array_map(function($flag) {
			switch ($flag) {
				case 'fit': return NetteImage::FIT; break;
				case 'fill': return NetteImage::FILL; break;
				case 'exact': return NetteImage::EXACT; break;
				case 'shrinkOnly': return NetteImage::SHRINK_ONLY; break;
				case 'stretch': return NetteImage::STRETCH; break;
				default:
					throw new InvalidArgumentException('Unknown resize flag "'. $flag. '".');
					break;
			}
		}, $flags);

		$result = 0;
		foreach ($flags as $flag) {
			$result = $result | $flag;
		}

		return $result;
	}


	/**
	 * @param int|string $size
	 * @return array
	 * @throws \Carrooi\ImagesManager\InvalidArgumentException
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

		return [$width, $height];
	}

}
