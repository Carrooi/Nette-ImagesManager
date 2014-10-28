<?php

namespace DK\ImagesManager;

use Nette\Object;
use Nette\Utils\Image as NetteImage;
use Nette\Utils\Strings;
use Nette\Utils\Finder;
use Nette\Http\Request;
use DK\ImagesManager\Latte\Helpers as TemplateHelpers;

/**
 *
 * @author David Kudera
 */
class ImagesManager extends Object
{


	/** @var \Nette\Http\Request */
	private $httpRequest;

	/** @var \DK\ImagesManager\INameResolver */
	private $nameResolver;

	/** @var string */
	private $basePath;

	/** @var string */
	private $baseUrl;

	/** @var string */
	private $imagesMask;

	/** @var string */
	private $thumbnailsMask;

	/** @var string */
	private $resizeFlag;

	/** @var string */
	private $default;

	/** @var int */
	private $quality;

	/** @var \DK\ImagesManager\NamespaceManager[] */
	private $namespaces = array();


	/**
	 * @param \DK\ImagesManager\INameResolver $nameResolver
	 * @param string $basePath
	 * @param string $baseUrl
	 * @param string $imagesMask
	 * @param string $thumbnailsMask
	 * @param string $resizeFlag
	 * @param string $default
	 * @param int $quality
	 * @param \Nette\Http\Request $httpRequest
	 */
	public function __construct(INameResolver $nameResolver, $basePath, $baseUrl, $imagesMask, $thumbnailsMask, $resizeFlag, $default, $quality, Request $httpRequest)
	{
		$this->nameResolver = $nameResolver;
		$this->httpRequest = $httpRequest;
		$this->basePath = $basePath;
		$this->baseUrl = $baseUrl;
		$this->imagesMask = $imagesMask;
		$this->thumbnailsMask = $thumbnailsMask;
		$this->resizeFlag = $resizeFlag;
		$this->default = $default;
		$this->quality = $quality;
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
	 * @return string
	 */
	public function getThumbnailsMask()
	{
		return $this->thumbnailsMask;
	}


	/**
	 * @param string $name
	 * @param \DK\ImagesManager\NamespaceManager $namespaceManager
	 * @return \DK\ImagesManager\ImagesManager
	 */
	public function addNamespace($name, NamespaceManager $namespaceManager)
	{
		$this->namespaces[$name] = $namespaceManager;
		$namespaceManager->registerImagesManager($this);
		return $this;
	}


	/**
	 * @param string $name
	 * @return \DK\ImagesManager\NamespaceManager
	 */
	public function getNamespace($name)
	{
		if (!isset($this->namespaces[$name])) {
			$manager = new NamespaceManager($name, $this->nameResolver);
			$manager
				->setDefault($this->default)
				->setResizeFlag($this->resizeFlag)
				->setQuality($this->quality);

			$this->addNamespace($name, $manager);
		}

		return $this->namespaces[$name];
	}


	/**
	 * @param string $namespace
	 * @return \DK\ImagesManager\Image[]
	 */
	public function findImages($namespace)
	{
		$path = $this->basePath. DIRECTORY_SEPARATOR. $this->imagesMask;
		$path = strtr($path, array(
			'<namespace>' => $namespace,
			'<separator>' => DIRECTORY_SEPARATOR,
			'<name>' => '*',
			'<extension>' => '*',
		));

		$pos = mb_strrpos($path, DIRECTORY_SEPARATOR);
		$directory = mb_substr($path, 0, $pos);
		$finderMask = mb_substr($path, $pos + 1);

		$mask = strtr($this->imagesMask, array(
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
	 * @param \DK\ImagesManager\Image $image
	 * @return \DK\ImagesManager\Image[]
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
	 * @return \DK\ImagesManager\Image
	 * @throws \DK\ImagesManager\ImageNotExistsException
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

		if (!$image->isExists() && $default !== false) {
			if ($default === null) {
				if (($default = $namespaceManager->getNameResolver()->getDefaultName($name)) === null) {
					$default = $namespaceManager->getDefault();
				}
			}

			if ($default !== null) {
				$image = $this->createImage($namespace, new ParsedName($default));
			}
		}

		if (!$image->isExists()) {
			throw new ImageNotExistsException('Image "'. $namespaceManager->getNameResolver()->translateName($name). '" does not exists.');
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
	 * @return \DK\ImagesManager\Image
	 */
	public function createImage($namespace, $name)
	{
		if ($name instanceof ParsedName) {
			$name = $name->getName();
		} else {
			$name = $this->getNamespace($namespace)->getNameResolver()->translateName($name);
		}

		$image = new Image($namespace, $name, $this->httpRequest);

		$image
			->setBasePath($this->getBasePath())
			->setBaseUrl($this->getBaseUrl())
			->setImagesMask($this->getImagesMask())
			->setThumbnailsMask($this->getThumbnailsMask());

		return $image;
	}


	/**
	 * @param \Nette\Utils\Image $image
	 * @param string $namespace
	 * @param mixed $name
	 * @param int $quality
	 * @return \DK\ImagesManager\Image
	 */
	public function upload(NetteImage $image, $namespace, $name, $quality = null)
	{
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
	 * @param \DK\ImagesManager\Image $image
	 */
	public function removeImage(Image $image)
	{
		if (!$image->isThumbnail()) {
			$this->removeThumbnails($image);
		}

		unlink($image->getPath());
	}


	/**
	 * @param \DK\ImagesManager\Image $image
	 */
	public function removeThumbnails(Image $image)
	{
		foreach ($this->findThumbnails($image) as $thumbnail) {
			unlink($thumbnail->getPath());
		}
	}


	/**
	 * @internal
	 * @return \DK\ImagesManager\Latte\Helpers
	 */
	public function createTemplateHelpers()
	{
		return new TemplateHelpers($this);
	}

}
 