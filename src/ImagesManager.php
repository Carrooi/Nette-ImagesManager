<?php

namespace Carrooi\ImagesManager;

use Nette\Http\Url;
use Nette\Object;
use Nette\Utils\Image as NetteImage;
use Nette\Utils\Strings;
use Nette\Utils\Finder;
use Carrooi\ImagesManager\Latte\Helpers as TemplateHelpers;

/**
 *
 * @author David Kudera
 */
class ImagesManager extends Object
{


	const DEFAULT_IMAGES_MASK = '<namespace><separator><name>.<extension>';

	const DEFAULT_THUMBNAILS_MASK = '<namespace><separator><name>_<resizeFlag>_<size>.<extension>';

	const DEFAULT_RESIZE_FLAG = 'fit';

	const DEFAULT_DEFAULT_IMAGE = 'default.jpg';

	const DEFAULT_QUALITY = 90;


	/** @var \Carrooi\ImagesManager\IImagesStorage */
	private $storage;

	/** @var \Carrooi\ImagesManager\INameResolver */
	private $nameResolver;

	/** @var string */
	private $host;

	/** @var string */
	private $basePath;

	/** @var string */
	private $baseUrl;

	/** @var string */
	private $imagesMask = self::DEFAULT_IMAGES_MASK;

	/** @var string */
	private $thumbnailsMask = self::DEFAULT_THUMBNAILS_MASK;

	/** @var string */
	private $resizeFlag = self::DEFAULT_RESIZE_FLAG;

	/** @var string */
	private $default = self::DEFAULT_DEFAULT_IMAGE;

	/** @var int */
	private $quality = self::DEFAULT_QUALITY;

	/** @var \Carrooi\ImagesManager\NamespaceManager[] */
	private $namespaces = array();


	/**
	 * @param \Carrooi\ImagesManager\INameResolver $nameResolver
	 * @param string $basePath
	 * @param string $baseUrl
	 * @param \Carrooi\ImagesManager\IImagesStorage $storage
	 */
	public function __construct(INameResolver $nameResolver, $basePath, $baseUrl, IImagesStorage $storage)
	{
		$this->nameResolver = $nameResolver;
		$this->basePath = $basePath;
		$this->baseUrl = $baseUrl;
		$this->storage = $storage;
	}


	/**
	 * @return string
	 */
	public function getResizeFlag()
	{
		return $this->resizeFlag;
	}


	/**
	 * @param string $resizeFlag
	 * @return $this
	 */
	public function setResizeFlag($resizeFlag)
	{
		$this->resizeFlag = $resizeFlag;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getDefault()
	{
		return $this->default;
	}


	/**
	 * @param string $default
	 * @return $this
	 */
	public function setDefault($default)
	{
		$this->default = $default;
		return $this;
	}


	/**
	 * @return int
	 */
	public function getQuality()
	{
		return $this->quality;
	}


	/**
	 * @param int $quality
	 * @return $this
	 */
	public function setQuality($quality)
	{
		$this->quality = $quality;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}


	/**
	 * @param string $host
	 * @return $this
	 */
	public function setHost($host)
	{
		$this->host = $host;
		return $this;
	}


	/**
	 * @param \Nette\Http\Url $url
	 * @return $this
	 */
	public function setHostFromUrl(Url $url)
	{
		$this->host = $url->getScheme(). '://'. $url->getHost();
		return $this;
	}


	/**
	 * @return string
	 */
	public function getBasePath()
	{
		return $this->basePath;
	}


	/**
	 * @return string
	 */
	public function getBaseUrl()
	{
		return $this->baseUrl;
	}


	/**
	 * @return string
	 */
	public function getImagesMask()
	{
		return $this->imagesMask;
	}


	/**
	 * @param string $imagesMask
	 * @return $this
	 */
	public function setImagesMask($imagesMask)
	{
		$this->imagesMask = $imagesMask;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getThumbnailsMask()
	{
		return $this->thumbnailsMask;
	}


	/**
	 * @param string $thumbnailsMask
	 * @return $this
	 */
	public function setThumbnailsMask($thumbnailsMask)
	{
		$this->thumbnailsMask = $thumbnailsMask;
		return $this;
	}


	/**
	 * @param string $name
	 * @param \Carrooi\ImagesManager\NamespaceManager $namespaceManager
	 * @return \Carrooi\ImagesManager\ImagesManager
	 */
	public function addNamespace($name, NamespaceManager $namespaceManager)
	{
		$this->namespaces[$name] = $namespaceManager;
		$namespaceManager->registerImagesManager($this);

		if (!$namespaceManager->getResizeFlag()) {
			$namespaceManager->setResizeFlag($this->getResizeFlag());
		}

		if (!$namespaceManager->hasDefault()) {
			$namespaceManager->setDefault($this->getDefault());
		}

		if (!$namespaceManager->getQuality()) {
			$namespaceManager->setQuality($this->getQuality());
		}

		return $this;
	}


	/**
	 * @param string $name
	 * @return \Carrooi\ImagesManager\NamespaceManager
	 */
	public function getNamespace($name)
	{
		if (!isset($this->namespaces[$name])) {
			$manager = new NamespaceManager($name, $this->nameResolver);
			$manager
				->setDefault($this->getDefault())
				->setResizeFlag($this->getResizeFlag())
				->setQuality($this->getQuality());

			$this->addNamespace($name, $manager);
		}

		return $this->namespaces[$name];
	}


	/**
	 * @param string $namespace
	 * @return \Carrooi\ImagesManager\Image[]
	 */
	public function findImages($namespace)
	{
		$path = $this->basePath. DIRECTORY_SEPARATOR. $this->getImagesMask();
		$path = strtr($path, array(
			'<namespace>' => $namespace,
			'<separator>' => DIRECTORY_SEPARATOR,
			'<name>' => '*',
			'<extension>' => '*',
		));

		$pos = mb_strrpos($path, DIRECTORY_SEPARATOR);
		$directory = mb_substr($path, 0, $pos);
		$finderMask = mb_substr($path, $pos + 1);

		$mask = strtr($this->getImagesMask(), array(
			'<namespace>' => $namespace,
			'<separator>' => DIRECTORY_SEPARATOR,
			'<name>' => '%name%',
			'<extension>' => '%extension%',
		));
		$mask = '/'. preg_quote($mask, '/'). '$/';
		$mask = strtr($mask, array(
			'%name%' => '(?P<name>'. Image::NAME_REGEX. ')',
			'%extension%' => '(?P<extension>[a-zA-Z]{3,4})'
		));

		$result = array();
		foreach (Finder::findFiles($finderMask)->in($directory) as $name => $file) {
			if ($match = Strings::match($name, $mask)) {
				$result[] = $this->createImage($namespace, new ParsedName($match['name']. '.'. $match['extension']));
			}
		}

		return $result;
	}


	/**
	 * @param \Carrooi\ImagesManager\Image $image
	 * @return \Carrooi\ImagesManager\Image[]
	 */
	public function findThumbnails(Image $image)
	{
		$path = $image->getBasePath(). DIRECTORY_SEPARATOR. $image->getThumbnailsMask();

		$path = Helpers::expandFromImage($path, $image, false);

		$pos = mb_strrpos($path, DIRECTORY_SEPARATOR);
		$directory = mb_substr($path, 0, $pos);
		$mask = mb_substr($path, $pos + 1);

		$finderMask = Strings::replace($mask, '/(\<[a-zA-Z]+\>)/', '*');

		$result = array();
		foreach (Finder::findFiles($finderMask)->in($directory) as $name => $file) {
			$info = Helpers::parseFileName($name, $mask);

			if ($info) {
				$result[] = $this->createImage($image->getNamespace(), new ParsedName($image->getName()))
					->setSize($info->size)
					->setResizeFlag($info->resizeFlag);
			}
		}

		return $result;
	}


	/**
	 * @param string $namespace
	 * @param mixed $name
	 * @param string|int $size
	 * @param string $resizeFlag
	 * @param string $default
	 * @param int $quality
	 * @return \Carrooi\ImagesManager\Image
	 * @throws \Carrooi\ImagesManager\ImageNotExistsException
	 */
	public function load($namespace, $name, $size = null, $resizeFlag = null, $default = null, $quality = null)
	{
		$namespaceManager = $this->getNamespace($namespace);

		if ($resizeFlag === null) {
			$resizeFlag = $namespaceManager->getResizeFlag();
		}

		if ($quality === null) {
			$quality = $namespaceManager->getQuality();
		}

		$image = $this->createImage($namespace, $name);
		$translatedName = $namespaceManager->getNameResolver()->translateName($name);

		if (($image === null || !$image->isExists()) && $default !== false) {
			if ($default === null) {
				if (($default = $namespaceManager->getNameResolver()->getDefaultName($name)) === null) {
					if (($default = $this->storage->getDefault($namespace, $translatedName)) === null) {
						$default = $namespaceManager->getDefault();

						if ($default !== null) {
							$this->storage->storeDefault($namespace, $translatedName, $default);
						}
					}
				}
			}

			if ($default !== null) {
				$image = $this->createImage($namespace, new ParsedName($default));
			}
		}

		if ($image === null || !$image->isExists()) {
			throw new ImageNotExistsException('Image "'. $translatedName. '" does not exists.');
		}

		if ($resizeFlag !== null) {
			$image->setResizeFlag($resizeFlag);
		}

		if ($size !== null) {
			$image->setSize($size)->tryCreateThumbnail($quality);
		}

		return $image;
	}


	/**
	 * @param string $namespace
	 * @param mixed $name
	 * @return \Carrooi\ImagesManager\Image
	 */
	public function createImage($namespace, $name)
	{
		if (($name = $this->getFullName($namespace, $name)) === null) {
			return null;
		}

		$image = new Image($namespace, $name);

		$image
			->setHost($this->host)
			->setBasePath($this->getBasePath())
			->setBaseUrl($this->getBaseUrl())
			->setImagesMask($this->getImagesMask())
			->setThumbnailsMask($this->getThumbnailsMask());

		return $image;
	}


	/**
	 * @param string $namespace
	 * @param string $name
	 * @return string
	 */
	private function getFullName($namespace, $name)
	{
		if ($name instanceof ParsedName) {
			$name = $name->getName();
		} else {
			$name = $this->getNamespace($namespace)->getNameResolver()->translateName($name);
		}

		if (($found = $this->storage->getFullName($namespace, $name)) !== null) {
			return $found;
		}

		$original = $name;

		if (pathinfo($name, PATHINFO_EXTENSION) === '') {
			if (($extension = $this->tryFindExtension($namespace, $name)) !== null) {
				$name .= ".$extension";
			} else {
				return null;
			}
		}

		$this->storage->storeAlias($namespace, $original, $name);

		return $name;
	}


	/**
	 * @param string $namespace
	 * @param string $name
	 * @return string
	 */
	private function tryFindExtension($namespace, $name)
	{
		$path = Helpers::expand($this->getBasePath(). DIRECTORY_SEPARATOR. $this->getImagesMask(), $namespace, $name, '*');
		$shortName = pathinfo($path, PATHINFO_BASENAME);
		$dir = pathinfo($path, PATHINFO_DIRNAME);

		foreach (Finder::findFiles($shortName)->in($dir) as $image => $file) {		/** @var $file \SplFileInfo */
			return pathinfo($image, PATHINFO_EXTENSION);
		}

		return null;
	}


	/**
	 * @param \Nette\Utils\Image $image
	 * @param string $namespace
	 * @param mixed $name
	 * @param int $quality
	 * @return \Carrooi\ImagesManager\Image
	 */
	public function upload(NetteImage $image, $namespace, $name, $quality = null)
	{
		if (($name = $this->getFullName($namespace, $name)) === null) {
			throw new InvalidImageNameException('Could not upload image with unknown name.');
		}

		$name = new ParsedName($name);

		$img = $this->createImage($namespace, $name);
		if ($img->isExists()) {
			$this->removeImage($img);
		}

		if ($quality === null) {
			$quality = $this->getNamespace($namespace)->getQuality();
		}

		$image->save($img->getPath(), $quality);

		return $img;
	}


	/**
	 * @param \Carrooi\ImagesManager\Image $image
	 */
	public function removeImage(Image $image)
	{
		if (!$image->isThumbnail()) {
			$this->removeThumbnails($image);
		}

		unlink($image->getPath());

		$this->storage->clear($image->getNamespace(), $image->getName());
	}


	/**
	 * @param \Carrooi\ImagesManager\Image $image
	 */
	public function removeThumbnails(Image $image)
	{
		foreach ($this->findThumbnails($image) as $thumbnail) {
			unlink($thumbnail->getPath());
		}
	}


	/**
	 * @internal
	 * @return \Carrooi\ImagesManager\Latte\Helpers
	 */
	public function createTemplateHelpers()
	{
		return new TemplateHelpers($this);
	}

}
