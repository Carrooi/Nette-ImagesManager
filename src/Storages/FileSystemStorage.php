<?php

namespace Carrooi\ImagesManager\Storages;

use Carrooi\ImagesManager\ConfigurationException;
use Carrooi\ImagesManager\Image\Image;
use Nette\Utils\Finder;
use Nette\Utils\Image as NetteImage;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class FileSystemStorage implements IStorage
{


	/** @var string */
	private $basePath;

	/** @var string */
	private $baseUrl;


	/**
	 * @param array $config
	 * @throws \Carrooi\ImagesManager\ConfigurationException
	 */
	public function __construct(array $config)
	{
		if (!isset($config['basePath'])) {
			throw new ConfigurationException('FileSystemStorage: missing configuration basePath.');
		}

		if (!isset($config['baseUrl'])) {
			throw new ConfigurationException('FileSystemStorage: missing configuration baseUrl.');
		}

		$this->basePath = $config['basePath'];
		$this->baseUrl = $config['baseUrl'];
	}


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $resizeFlag
	 * @return bool
	 */
	public function isImageExists(Image $image, $width = null, $height = null, $resizeFlag = null)
	{
		$path = $this->getRealPath($image, $width, $height, $resizeFlag);
		return is_file($path);
	}


	/**
	 * @param \Nette\Utils\Image $image
	 * @param \Carrooi\ImagesManager\Image\Image $img
	 * @param int|null $quality
	 */
	public function saveImage(NetteImage $image, Image $img, $quality = null)
	{
		$path = $this->getRealPath($img);
		$image->save($path, $quality);
	}


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 */
	public function removeImage(Image $image)
	{
		if (!$this->isImageExists($image)) {
			return;
		}

		$path = $this->getRealPath($image);
		unlink($path);

		foreach ($this->findThumbnails($image) as $thumbnail) {
			unlink($thumbnail);
		}
	}


	/**
	 * @param string $namespace
	 * @param string $name
	 * @return string|null
	 */
	public function getFullName($namespace, $name)
	{
		/** @var \SplFileInfo $file */
		foreach (Finder::findFiles($name . '.*')->from($this->basePath . DIRECTORY_SEPARATOR . $namespace) as $file) {
			break;
		}

		if (!isset($file)) {
			return null;
		}

		return $file->getBasename();
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
		return $this->baseUrl . '/' . $this->createBaseImagePath($image, '/', $width, $height, $resizeFlag);
	}


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $resizeFlag
	 * @return \Nette\Utils\Image
	 * @throws \Nette\Utils\UnknownImageFileException
	 */
	public function getNetteImage(Image $image, $width = null, $height = null, $resizeFlag = null)
	{
		$path = $this->getRealPath($image, $width, $height, $resizeFlag);
		return NetteImage::fromFile($path);
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
		$path = $this->getRealPath($image, $width, $height, $resizeFlag);
		$thumbnail->save($path, $quality);
	}


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 * @return array
	 */
	private function findThumbnails(Image $image)
	{
		$name = pathinfo($image->getName(), PATHINFO_FILENAME);
		$ext = pathinfo($image->getName(), PATHINFO_EXTENSION);

		$pattern = '^'. $name. '(_w\d+)?(_h\d+)?(_f\d+)?.'. $ext. '$';
		$nameFilter = function(\SplFileInfo $file) use ($pattern) {
			return preg_match('/'. $pattern. '/', $file->getBasename()) ? true : false;
		};

		$thumbnails = [];

		/** @var \SplFileInfo $file */
		foreach (Finder::findFiles($name. '*.'. $ext)->filter($nameFilter)->from($this->basePath. DIRECTORY_SEPARATOR. $image->getNamespace()) as $file) {
			$thumbnails[] = $file->getRealPath();
		}

		return $thumbnails;
	}


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $resizeFlag
	 * @return string
	 */
	private function getRealPath(Image $image, $width = null, $height = null, $resizeFlag = null)
	{
		return $this->basePath. DIRECTORY_SEPARATOR. $this->createBaseImagePath($image, DIRECTORY_SEPARATOR, $width, $height, $resizeFlag);
	}


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 * @param string $separator
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $resizeFlag
	 * @return string
	 */
	private function createBaseImagePath(Image $image, $separator, $width = null, $height = null, $resizeFlag = null)
	{
		$name = pathinfo($image->getName(), PATHINFO_FILENAME);
		$ext = pathinfo($image->getName(), PATHINFO_EXTENSION);

		return
			$image->getNamespace()
			. $separator. $name
			. ($width ? '_w'. $width : '')
			. ($height ? '_h'. $height : '')
			. (($width || $height) && $resizeFlag !== null ? '_f'. $resizeFlag : '')
			. '.'. $ext;
	}

}
