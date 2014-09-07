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

	/** @var array */
	private $namespaces = array();


	/**
	 * @param string $basePath
	 * @param string $baseUrl
	 * @param string $imagesMask
	 * @param string $thumbnailsMask
	 * @param string $resizeFlag
	 * @param string $default
	 * @param int $quality
	 * @param \Nette\Http\Request $httpRequest
	 */
	public function __construct($basePath, $baseUrl, $imagesMask, $thumbnailsMask, $resizeFlag, $default, $quality, Request $httpRequest)
	{
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
	 * @internal
	 * @param string $namespace
	 * @param array $definition
	 */
	public function setNamespaceDefinition($namespace, array $definition)
	{
		$this->namespaces[$namespace] = $definition;
	}


	/**
	 * @param string $namespace
	 * @return bool
	 */
	private function isNamespaceDefinition($namespace)
	{
		return isset($this->namespaces[$namespace]);
	}


	/**
	 * @param string $namespace
	 * @return array
	 * @throws \DK\ImagesManager\InvalidArgumentException
	 */
	private function &getNamespaceDefinition($namespace)
	{
		if (!$this->isNamespaceDefinition($namespace)) {
			throw new InvalidArgumentException('Images namespace "'. $namespace. '" is not registered.');
		}

		$namespace = &$this->namespaces[$namespace];

		return $namespace;
	}


	/**
	 * @param string $namespace
	 * @param string $name
	 * @return \DK\ImagesManager\Image[]
	 * @throws \DK\ImagesManager\InvalidArgumentException
	 */
	public function getList($namespace, $name)
	{
		$_namespace = &$this->getNamespaceDefinition($namespace);

		if (!isset($_namespace['lists'][$name])) {
			throw new InvalidArgumentException('Images list "'. $name. '" is not registered in "'. $namespace. '" namespace.');
		}

		if ($_namespace['lists'][$name]['parsed'] === null) {
			$_namespace['lists'][$name]['parsed'] = array();

			foreach ($_namespace['lists'][$name]['images'] as $image) {
				$_namespace['lists'][$name]['parsed'][] = $this->createImage($namespace, $image);
			}
		}

		return $_namespace['lists'][$name]['parsed'];
	}


	/**
	 * @param string $namespace
	 * @return string
	 */
	public function getResizeFlag($namespace)
	{
		if ($this->isNamespaceDefinition($namespace)) {
			$namespace = $this->getNamespaceDefinition($namespace);
			return $namespace['resizeFlag'];
		}

		return $this->resizeFlag;
	}


	/**
	 * @param string $namespace
	 * @return string
	 */
	public function getDefault($namespace)
	{
		$default = $this->default;

		if ($this->isNamespaceDefinition($namespace)) {
			$namespace = $this->getNamespaceDefinition($namespace);
			$default = $namespace['default'];
		}

		if (is_array($default)) {
			$default = $default[array_rand($default)];
		}

		return $default;
	}


	/**
	 * @param string $namespace
	 * @return string
	 */
	public function getQuality($namespace)
	{
		if ($this->isNamespaceDefinition($namespace)) {
			$namespace = $this->getNamespaceDefinition($namespace);
			return $namespace['quality'];
		}

		return $this->quality;
	}


	/**
	 * @param string $namespace
	 * @return \DK\ImagesManager\Image[]
	 *
	 * @todo: refactor
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
				$result[] = $this->createImage($namespace, $match['name']. '.'. $match['extension']);
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

		$path = Helpers::expand($path, $image, false);

		$pos = mb_strrpos($path, DIRECTORY_SEPARATOR);
		$directory = mb_substr($path, 0, $pos);
		$mask = mb_substr($path, $pos + 1);

		$finderMask = Strings::replace($mask, '/(\<[a-zA-Z]+\>)/', '*');

		$result = array();
		foreach (Finder::findFiles($finderMask)->in($directory) as $name => $file) {
			$info = Helpers::parseFileName($name, $mask);

			if ($info) {
				$result[] = $this->createImage($image->getNamespace(), $image->getName())
					->setSize($info->size)
					->setResizeFlag($info->resizeFlag);
			}
		}

		return $result;
	}


	/**
	 * @param string $namespace
	 * @param string $name
	 * @param string|int $size
	 * @param string $resizeFlag
	 * @param string $default
	 * @param int $quality
	 * @return \DK\ImagesManager\Image
	 * @throws \DK\ImagesManager\ImageNotExistsException
	 */
	public function load($namespace, $name, $size = null, $resizeFlag = null, $default = null, $quality = null)
	{
		if ($resizeFlag === null) {
			$resizeFlag = $this->getResizeFlag($namespace);
		}

		if ($default === null) {
			$default = $this->getDefault($namespace);
		}

		if ($quality === null) {
			$quality = $this->getQuality($namespace);
		}

		if ($name === null) {
			$name = $default;
			$default = null;
		}

		$image = $this->createImage($namespace, $name);

		if (!$image->isExists() && $default) {
			$image = $this->createImage($namespace, $default);
		}

		if (!$image->isExists()) {
			throw new ImageNotExistsException('Image "'. $name. '" does not exists.');
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
	 * @param string $name
	 * @return \DK\ImagesManager\Image
	 */
	public function createImage($namespace, $name)
	{
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
	 * @param string $name
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
			$quality = $this->quality;
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
 