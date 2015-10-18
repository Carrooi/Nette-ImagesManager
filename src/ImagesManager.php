<?php

namespace Carrooi\ImagesManager;

use Carrooi\ImagesManager\Caching\ICacheStorage;
use Carrooi\ImagesManager\Helpers\Validators;
use Carrooi\ImagesManager\Image\IImageFactory;
use Carrooi\ImagesManager\Image\Image;
use Carrooi\ImagesManager\Latte\Helpers;
use Carrooi\ImagesManager\Storages\IStorage;
use Nette\Utils\Image as NetteImage;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class ImagesManager
{


	/** @var \Carrooi\ImagesManager\Image\IImageFactory */
	private $imageFactory;

	/** @var \Carrooi\ImagesManager\Storages\IStorage */
	private $storage;

	/** @var \Carrooi\ImagesManager\Configuration */
	private $config;

	/** @var \Carrooi\ImagesManager\Caching\ICacheStorage */
	private $cacheStorage;


	/**
	 * @param \Carrooi\ImagesManager\Image\IImageFactory $imageFactory
	 * @param \Carrooi\ImagesManager\Storages\IStorage $storage
	 * @param \Carrooi\ImagesManager\Configuration $config
	 * @param \Carrooi\ImagesManager\Caching\ICacheStorage $cacheStorage
	 */
	public function __construct(IImageFactory $imageFactory, IStorage $storage, Configuration $config, ICacheStorage $cacheStorage)
	{
		$this->imageFactory = $imageFactory;
		$this->storage = $storage;
		$this->config = $config;
		$this->cacheStorage = $cacheStorage;
	}


	/**
	 * @param string $namespace
	 * @param string $name
	 * @param bool $needFullName
	 * @return string
	 */
	private function parseImageName($namespace, $name, $needFullName = false)
	{
		$name = $this->config->getNameResolver($namespace)->getName($name);

		$needFullName && Validators::validateImageFullName($name);

		if (!Validators::isImageFullName($name)) {
			if (!$fullName = $this->cacheStorage->getFullName($namespace, $name)) {

				if (!$fullName = $this->storage->getFullName($namespace, $name)) {
					Validators::validateImageFullName($name);
				}

				$this->cacheStorage->storeAlias($namespace, $fullName, $name);
			}

			$name = $fullName;
		}

		return $name;
	}


	/**
	 * @param string $namespace
	 * @param string $name
	 * @return string|null
	 */
	private function getDefaultImage($namespace, $name)
	{
		$default = $this->config->getNameResolver($namespace)->getDefaultName($name);

		if (!$default) {
			$default = $this->config->getDefaultImage($namespace);
		}

		return $default ? $default : null;
	}


	/**
	 * @param \Nette\Utils\Image $image
	 * @param string $namespace
	 * @param string $name
	 * @return \Carrooi\ImagesManager\Image\Image
	 */
	public function upload(NetteImage $image, $namespace, $name)
	{
		$name = $this->parseImageName($namespace, $name, true);

		try {
			$oldName = $this->parseImageName($namespace, pathinfo($name, PATHINFO_FILENAME));
			$oldImage = $this->imageFactory->create($namespace, $oldName);

			if ($this->storage->isImageExists($oldImage)) {
				$this->cacheStorage->clear($namespace, $oldName);
				$this->remove($oldImage);
			}
		} catch (InvalidImageNameException $e) {}

		$img = $this->imageFactory->create($namespace, $name);

		$this->storage->saveImage($image, $img, $this->config->getQuality($namespace));

		return $img;
	}


	/**
	 * @param string $namespace
	 * @param string $name
	 * @return \Carrooi\ImagesManager\Image\Image
	 */
	public function getImage($namespace, $name)
	{
		try {
			$fullName = $this->parseImageName($namespace, $name);
		} catch (InvalidImageNameException $e) {
			if (is_scalar($name) || is_object($name) && method_exists($name, '__toString')) {
				$name = " $name";
			} else {
				$name = '';
			}

			throw new ImageNotExistsException('Image'. $name. ' does not exists in namespace '. $namespace. '.');
		}

		$name = $fullName;

		$image = $this->imageFactory->create($namespace, $name);

		if (!$this->storage->isImageExists($image)) {
			throw new ImageNotExistsException('Image '. $name. ' does not exists in namespace '. $namespace. '.');
		}

		return $image;
	}


	/**
	 * @param string $namespace
	 * @param string $name
	 * @return \Carrooi\ImagesManager\Image\Image|null
	 */
	public function findImage($namespace, $name)
	{
		try {
			$image = $this->getImage($namespace, $name);
		} catch (ImageNotExistsException $e) {
			$default = $this->getDefaultImage($namespace, $name);

			try {
				$image = $this->getImage($namespace, $default);
			} catch (ImageNotExistsException $e) {
				$image = null;
			}
		}

		return isset($image) ? $image : null;
	}


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 */
	public function remove(Image $image)
	{
		$this->storage->removeImage($image);
	}


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $resizeFlag
	 * @return string
	 */
	public function getUrl(Image $image, $width = null, $height = null, $resizeFlag = null)
	{
		$resizeFlag = $resizeFlag === null ? $this->config->getResizeFlag($image->getNamespace()) : $resizeFlag;

		return $this->storage->getImageUrl($image, $width, $height, $resizeFlag);
	}


	/**
	 * @param string $namespace
	 * @param int|null $width
	 * @param int|null $height
	 * @return string|null
	 */
	public function getDummyImageUrl($namespace, $width = null, $height = null)
	{
		if (!$this->config->isDummyEnabled($namespace)) {
			return null;
		}

		if (rand(0, 99) > $this->config->getDummyDisplayChance($namespace)) {
			return null;
		}

		if ($width === null || $height === null) {
			if (!$fallbackSize = $this->config->getDummyFallbackSize($namespace)) {
				return null;
			}

			$width = $fallbackSize[0];
			$height = $fallbackSize[1];
		}

		$category = $this->config->getDummyCategory($namespace);
		$provider = $this->config->getDummyProvider($namespace);

		return $provider->getUrl($width, $height, $category);
	}


	/**
	 * @param \Carrooi\ImagesManager\Image\Image $image
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $resizeFlag
	 * @return \Nette\Utils\Image
	 */
	public function tryStoreThumbnail(Image $image, $width = null, $height = null, $resizeFlag = null)
	{
		$resizeFlag = $resizeFlag === null ? $this->config->getResizeFlag($image->getNamespace()) : $resizeFlag;

		if ($this->storage->isImageExists($image, $width, $height, $resizeFlag)) {
			return $this->storage->getNetteImage($image, $width, $height, $resizeFlag);
		}

		$img = $this->storage->getNetteImage($image);
		$img->resize($width, $height, $resizeFlag);

		$quality = $this->config->getQuality($image->getNamespace());

		$this->storage->tryStoreThumbnail($image, $img, $width, $height, $resizeFlag, $quality);

		return $img;
	}


	/**
	 * @return \Carrooi\ImagesManager\Latte\Helpers
	 */
	public function createTemplateHelpers()
	{
		return new Helpers($this);
	}

}
