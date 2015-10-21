<?php

namespace Carrooi\ImagesManager\Storages;

use Carrooi\ImagesManager\Image\Image;
use Nette\Utils\Image as NetteImage;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
interface IStorage
{


	/**
	 * Used in DI extension
	 *
	 * @param array $config
	 */
	//public function __construct(array $config);


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $resizeFlag
	 * @return bool
	 */
	public function isImageExists(Image $image, $width = null, $height = null, $resizeFlag = null);


	/**
	 * @param \Nette\Utils\Image $image
	 * @param \Carrooi\ImagesManager\Image\Image $img
	 * @param int|null $quality
	 */
	public function saveImage(NetteImage $image, Image $img, $quality = null);


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 */
	public function removeImage(Image $image);


	/**
	 * @param string $namespace
	 * @param string $name
	 * @return string|null
	 */
	public function getFullName($namespace, $name);


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $resizeFlag
	 * @return string
	 */
	public function getImageUrl(Image $image, $width = null, $height = null, $resizeFlag = null);


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $resizeFlag
	 * @return \Nette\Utils\Image
	 */
	public function getNetteImage(Image $image, $width = null, $height = null, $resizeFlag = null);


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 * @param \Nette\Utils\Image $thumbnail
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $resizeFlag
	 * @param int|null $quality
	 */
	public function tryStoreThumbnail(Image $image, NetteImage $thumbnail, $width = null, $height = null, $resizeFlag = null, $quality = null);

}
