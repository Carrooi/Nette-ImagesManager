<?php

namespace Carrooi\ImagesManager;

use Carrooi\ImagesManager\Dummy\IDummyImageProvider;
use Carrooi\ImagesManager\Dummy\LorempixelDummyImageProvider;
use Carrooi\ImagesManager\Naming\DefaultNameResolver;
use Carrooi\ImagesManager\Naming\INameResolver;
use Nette\Utils\Image;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class Configuration
{


	const DEFAULT_NAMESPACE_NAME = 'default';

	const CONFIG_DEFAULT_IMAGE_NAME = 'default';
	const CONFIG_RESIZE_FLAG = 'resizeFlag';
	const CONFIG_QUALITY = 'quality';
	const CONFIG_NAME_RESOLVER = 'nameResolver';
	const CONFIG_DUMMY_ENABLED = 'dummyEnabled';
	const CONFIG_DUMMY_PROVIDER = 'dummyProvider';
	const CONFIG_DUMMY_CATEGORY = 'dummyCategory';
	const CONFIG_DUMMY_FALLBACK_SIZE = 'dummyFallbackSize';
	const CONFIG_DUMMY_CHANCE = 'dummyChance';


	/** @var array */
	private $config = [];


	public function __construct()
	{
		$this->createEmptyConfiguration(self::DEFAULT_NAMESPACE_NAME);
	}


	/**
	 * @param string $namespace
	 * @return string
	 */
	public function getDefaultImage($namespace)
	{
		return $this->getConfiguration($namespace, self::CONFIG_DEFAULT_IMAGE_NAME);
	}


	/**
	 * @param string $namespace
	 * @param string $defaultImage
	 */
	public function setDefaultImage($namespace, $defaultImage)
	{
		$this->setConfiguration($namespace, self::CONFIG_DEFAULT_IMAGE_NAME, $defaultImage);
	}


	/**
	 * @param string $namespace
	 * @return int
	 */
	public function getResizeFlag($namespace)
	{
		return $this->getConfiguration($namespace, self::CONFIG_RESIZE_FLAG);
	}


	/**
	 * @param string $namespace
	 * @param int $resizeFlag
	 */
	public function setResizeFlag($namespace, $resizeFlag)
	{
		$this->setConfiguration($namespace, self::CONFIG_RESIZE_FLAG, $resizeFlag);
	}


	/**
	 * @param string $namespace
	 * @return int
	 */
	public function getQuality($namespace)
	{
		return $this->getConfiguration($namespace, self::CONFIG_QUALITY);
	}


	/**
	 * @param string $namespace
	 * @param int $quality
	 */
	public function setQuality($namespace, $quality)
	{
		$this->setConfiguration($namespace, self::CONFIG_QUALITY, $quality);
	}


	/**
	 * @param string $namespace
	 * @return \Carrooi\ImagesManager\Naming\INameResolver
	 */
	public function getNameResolver($namespace)
	{
		return $this->getConfiguration($namespace, self::CONFIG_NAME_RESOLVER);
	}


	/**
	 * @param string $namespace
	 * @param \Carrooi\ImagesManager\Naming\INameResolver $nameResolver
	 */
	public function setNameResolver($namespace, INameResolver $nameResolver)
	{
		$this->setConfiguration($namespace, self::CONFIG_NAME_RESOLVER, $nameResolver);
	}


	/**
	 * @param string $namespace
	 * @return bool
	 */
	public function isDummyEnabled($namespace)
	{
		return $this->getConfiguration($namespace, self::CONFIG_DUMMY_ENABLED);
	}


	/**
	 * @param string $namespace
	 * @param bool $enabled
	 */
	public function enableDummy($namespace, $enabled = true)
	{
		$this->setConfiguration($namespace, self::CONFIG_DUMMY_ENABLED, $enabled);
	}


	/**
	 * @param string $namespace
	 * @return \Carrooi\ImagesManager\Dummy\IDummyImageProvider
	 */
	public function getDummyProvider($namespace)
	{
		return $this->getConfiguration($namespace, self::CONFIG_DUMMY_PROVIDER);
	}


	/**
	 * @param string $namespace
	 * @param \Carrooi\ImagesManager\Dummy\IDummyImageProvider $dummyProvider
	 */
	public function setDummyProvider($namespace, IDummyImageProvider $dummyProvider)
	{
		$this->setConfiguration($namespace, self::CONFIG_DUMMY_PROVIDER, $dummyProvider);
	}


	/**
	 * @param string $namespace
	 * @return string|null
	 */
	public function getDummyCategory($namespace)
	{
		return $this->getConfiguration($namespace, self::CONFIG_DUMMY_CATEGORY);
	}


	/**
	 * @param string $namespace
	 * @param string $category
	 */
	public function setDummyCategory($namespace, $category)
	{
		$this->setConfiguration($namespace, self::CONFIG_DUMMY_CATEGORY, $category);
	}


	/**
	 * @param string $namespace
	 * @return array|null
	 */
	public function getDummyFallbackSize($namespace)
	{
		return $this->getConfiguration($namespace, self::CONFIG_DUMMY_FALLBACK_SIZE);
	}


	/**
	 * @param string $namespace
	 * @param int $width
	 * @param int $height
	 */
	public function setDummyFallbackSize($namespace, $width, $height)
	{
		$this->setConfiguration($namespace, self::CONFIG_DUMMY_FALLBACK_SIZE, [$width, $height]);
	}


	/**
	 * @param string $namespace
	 * @return int
	 */
	public function getDummyDisplayChance($namespace)
	{
		return $this->getConfiguration($namespace, self::CONFIG_DUMMY_CHANCE);
	}


	/**
	 * @param string $namespace
	 * @param int $chance
	 */
	public function setDummyDisplayChance($namespace, $chance)
	{
		$this->setConfiguration($namespace, self::CONFIG_DUMMY_CHANCE, $chance);
	}


	/**
	 * @param string $namespace
	 * @param string $type
	 * @return mixed
	 * @throws \Carrooi\ImagesManager\InvalidArgumentException
	 */
	private function getConfiguration($namespace, $type)
	{
		if (!isset($this->config[$namespace])) {
			$namespace = self::DEFAULT_NAMESPACE_NAME;
		}

		if (!array_key_exists($type, $this->config[$namespace])) {
			throw new InvalidArgumentException('Unknown configuration type '. $type. '.');
		}

		return $this->config[$namespace][$type];
	}


	/**
	 * @param string $namespace
	 * @param string $type
	 * @param mixed $value
	 * @return mixed
	 */
	private function setConfiguration($namespace, $type, $value)
	{
		if (!isset($this->config[$namespace])) {
			$this->createEmptyConfiguration($namespace);
		}

		$this->config[$namespace][$type] = $value;
	}


	/**
	 * @param string $namespace
	 */
	private function createEmptyConfiguration($namespace)
	{
		$getDefault = function($type, $fallback) use ($namespace) {
			return $namespace !== self::DEFAULT_NAMESPACE_NAME ? $this->getConfiguration(self::DEFAULT_NAMESPACE_NAME, $type) : $fallback;
		};

		$this->config[$namespace] = [
			self::CONFIG_DEFAULT_IMAGE_NAME => $getDefault(self::CONFIG_DEFAULT_IMAGE_NAME, 'default.jpg'),
			self::CONFIG_RESIZE_FLAG => $getDefault(self::CONFIG_RESIZE_FLAG, Image::FIT),
			self::CONFIG_QUALITY => $getDefault(self::CONFIG_QUALITY, null),
			self::CONFIG_NAME_RESOLVER => $getDefault(self::CONFIG_NAME_RESOLVER, new DefaultNameResolver),
			self::CONFIG_DUMMY_ENABLED => $getDefault(self::CONFIG_DUMMY_ENABLED, false),
			self::CONFIG_DUMMY_PROVIDER => $getDefault(self::CONFIG_DUMMY_PROVIDER, new LorempixelDummyImageProvider),
			self::CONFIG_DUMMY_CATEGORY => $getDefault(self::CONFIG_DUMMY_CATEGORY, null),
			self::CONFIG_DUMMY_FALLBACK_SIZE => $getDefault(self::CONFIG_DUMMY_FALLBACK_SIZE, null),
			self::CONFIG_DUMMY_CHANCE => $getDefault(self::CONFIG_DUMMY_CHANCE, 100),
		];
	}

}
