<?php

namespace DK\ImagesManager;

use Nette\Object;
use Nette\Utils\Image as NetteImage;
use Nette\Utils\Strings;
use Nette\Http\Request;

/**
 *
 * @author David Kudera
 */
class Image extends Object
{


	const NAME_REGEX = '[a-zA-Z0-9]+';


	/** @var \Nette\Http\Request */
	private $httpRequest;

	/** @var string */
	private $namespace;

	/** @var string */
	private $name;

	/** @var string */
	private $extension;

	/** @var int */
	private $width;

	/** @var int */
	private $height;

	/** @var string */
	private $resizeFlag;

	/** @var string */
	private $basePath;

	/** @var string */
	private $baseUrl;

	/** @var string */
	private $imagesMask;

	/** @var string */
	private $thumbnailsMask;


	/**
	 * @param string $namespace
	 * @param string $name
	 * @param \Nette\Http\Request $httpRequest
	 */
	public function __construct($namespace, $name, Request $httpRequest)
	{
		$this->httpRequest = $httpRequest;

		$this->namespace = $namespace;

		$this->setName($name);
	}


	/**
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}


	/**
	 * @param bool $full
	 * @return string
	 */
	public function getName($full = true)
	{
		return $full ? $this->name. '.'. $this->extension : $this->name;
	}


	/**
	 * @param string $name
	 * @return \DK\ImagesManager\Image
	 */
	public function setName($name)
	{
		$name = Helpers::parseName($name);
		$this->name = $name->name;
		$this->extension = $name->extension;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getExtension()
	{
		return $this->extension;
	}


	/**
	 * @return int|string
	 */
	public function getSize()
	{
		return $this->height === null ? $this->width : $this->width. 'x'. $this->height;
	}


	/**
	 * @param string|int $size
	 * @return \DK\ImagesManager\Image
	 */
	public function setSize($size)
	{
		$size = Helpers::parseSize($size);
		$this->width = $size->width;
		$this->height = $size->height;

		return $this;
	}


	/**
	 * @return int
	 */
	public function getWidth()
	{
		return $this->width;
	}


	/**
	 * @param int $width
	 * @return \DK\ImagesManager\Image
	 */
	public function setWidth($width)
	{
		$this->width = (int) $width;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function hasHeight()
	{
		return $this->height !== null;
	}


	/**
	 * @return int
	 */
	public function getHeight()
	{
		return $this->height;
	}


	/**
	 * @param int $height
	 * @return \DK\ImagesManager\Image
	 */
	public function setHeight($height)
	{
		$this->height = (int) $height;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getResizeFlag()
	{
		return $this->resizeFlag;
	}


	/**
	 * @param string $flag
	 * @return \DK\ImagesManager\Image
	 */
	public function setResizeFlag($flag)
	{
		$this->resizeFlag = $flag;
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
	 * @param string $path
	 * @return \DK\ImagesManager\Image
	 */
	public function setBasePath($path)
	{
		$this->basePath = $path;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getBaseUrl()
	{
		return $this->baseUrl;
	}


	/**
	 * @param string $url
	 * @return \DK\ImagesManager\Image
	 */
	public function setBaseUrl($url)
	{
		$this->baseUrl = $url;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getImagesMask()
	{
		return $this->imagesMask;
	}


	/**
	 * @param string $mask
	 * @return \DK\ImagesManager\Image
	 */
	public function setImagesMask($mask)
	{
		$this->imagesMask = $mask;
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
	 * @param string $mask
	 * @return \DK\ImagesManager\Image
	 */
	public function setThumbnailsMask($mask)
	{
		$this->thumbnailsMask = $mask;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isThumbnail()
	{
		return $this->width !== null;
	}


	/**
	 * @return string
	 */
	public function getExpandedName()
	{
		if ($this->isThumbnail()) {
			return Helpers::expandFromImage($this->thumbnailsMask, $this);
		} else {
			return Helpers::expandFromImage($this->imagesMask, $this);
		}
	}


	/**
	 * @return string
	 */
	public function getOriginalPath()
	{
		return $this->basePath. DIRECTORY_SEPARATOR. Helpers::expandFromImage($this->imagesMask, $this);
	}


	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->basePath. DIRECTORY_SEPARATOR. $this->getExpandedName();
	}


	/**
	 * @return bool
	 */
	public function isExists()
	{
		return is_file($this->getPath());
	}


	/**
	 * @param bool $absolute
	 * @return string
	 */
	public function getUrl($absolute = false)
	{
		$url = $this->baseUrl. '/'. $this->getExpandedName();

		if (!Strings::startsWith($url, '/')) {
			$url = '/'. $url;
		}

		if ($absolute) {
			$u = $this->httpRequest->getUrl();
			$url = $u->getScheme(). '://'. $u->getHost(). $url;
		}

		return $url;
	}


	/**
	 * @param int $quality
	 * @return \DK\ImagesManager\Image
	 * @throws \DK\ImagesManager\InvalidStateException
	 */
	public function tryCreateThumbnail($quality = null)
	{
		if (!$this->isThumbnail()) {
			throw new InvalidStateException('Can not create thumbnail for "'. $this->getName(). '" image when size is not provided.');
		}

		if ($this->isExists()) {
			return $this;
		}

		$flag = Helpers::getNetteResizeFlag($this->getResizeFlag());

		$image = NetteImage::fromFile($this->getOriginalPath())->resize($this->width, $this->height, $flag);
		$image->save($this->getPath(), $quality);

		return $this;
	}

}
 