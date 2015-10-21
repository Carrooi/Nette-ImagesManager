<?php

namespace CarrooiTests\Mocks;

use Carrooi\ImagesManager\Image\Image;
use Carrooi\ImagesManager\Storages\IStorage;
use Nette\Utils\Image as NetteImage;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class StorageMock implements IStorage
{


	/** @var bool */
	public $exists = true;


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $resizeFlag
	 * @return bool
	 */
	public function isImageExists(Image $image, $width = null, $height = null, $resizeFlag = null)
	{
		return $this->exists;
	}


	/**
	 * @param \Nette\Utils\Image $image
	 * @param \Carrooi\ImagesManager\Image\Image $img
	 * @param int|null $quality
	 */
	public function saveImage(NetteImage $image, Image $img, $quality = null)
	{

	}


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 */
	public function removeImage(Image $image)
	{

	}


	/**
	 * @param string $namespace
	 * @param string $name
	 * @return string|null
	 */
	public function getFullName($namespace, $name)
	{

	}


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $resizeFlag
	 * @return string
	 */
	public function getImageUrl(Image $image, $width = null, $height = null, $resizeFlag = null)
	{
		$name = pathinfo($image->getName(), PATHINFO_FILENAME);
		$ext = pathinfo($image->getName(), PATHINFO_EXTENSION);

		return
			'http://localhost/'
			. $image->getNamespace()
			. '/'. $name
			. ($width ? '_w'. $width : '')
			. ($height ? '_h'. $height : '')
			. (($width || $height) && $resizeFlag !== null ? '_f'. $resizeFlag : '')
			. '.'. $ext;
	}


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $resizeFlag
	 * @return \Nette\Utils\Image
	 */
	public function getNetteImage(Image $image, $width = null, $height = null, $resizeFlag = null)
	{
		return NetteImage::fromString(base64_decode('R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='));
	}


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 * @param \Nette\Utils\Image $thumbnail
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $resizeFlag
	 * @param int|null $quality
	 */
	public function tryStoreThumbnail(Image $image, NetteImage $thumbnail, $width = null, $height = null, $resizeFlag = null, $quality = null)
	{

	}

}
