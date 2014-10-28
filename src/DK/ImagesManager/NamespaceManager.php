<?php

namespace DK\ImagesManager;

use Nette\Object;

/**
 *
 * @author David Kudera
 */
class NamespaceManager extends Object
{


	/** @var string */
	private $name;

	/** @var \DK\ImagesManager\ImagesManager */
	private $imagesManager;

	/** @var \DK\ImagesManager\INameResolver */
	private $nameResolver;

	/** @var string|array */
	private $default;

	/** @var string */
	private $resizeFlag;

	/** @var int */
	private $quality;

	/** @var array */
	private $lists = array();


	/**
	 * @param string $name
	 * @param \DK\ImagesManager\INameResolver $nameResolver
	 */
	public function __construct($name, INameResolver $nameResolver)
	{
		$this->name = $name;
		$this->setNameResolver($nameResolver);
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @return \DK\ImagesManager\INameResolver
	 */
	public function getNameResolver()
	{
		return $this->nameResolver;
	}


	/**
	 * @param \DK\ImagesManager\INameResolver $nameResolver
	 * @return \DK\ImagesManager\NamespaceManager
	 */
	public function setNameResolver(INameResolver $nameResolver)
	{
		$this->nameResolver = $nameResolver;
		return $this;
	}


	/**
	 * @internal
	 * @param \DK\ImagesManager\ImagesManager $imagesManager
	 * @return \DK\ImagesManager\NamespaceManager
	 */
	public function registerImagesManager(ImagesManager $imagesManager)
	{
		$this->imagesManager = $imagesManager;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getDefault()
	{
		return is_string($this->default) ? $this->default : $this->default[array_rand($this->default)];
	}


	/**
	 * @param string|array $default
	 * @return \DK\ImagesManager\NamespaceManager
	 */
	public function setDefault($default)
	{
		$this->default = $default;
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
	 * @param string $resizeFlag
	 * @return \DK\ImagesManager\NamespaceManager
	 */
	public function setResizeFlag($resizeFlag)
	{
		$this->resizeFlag = $resizeFlag;
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
	 * @return \DK\ImagesManager\NamespaceManager
	 */
	public function setQuality($quality)
	{
		$this->quality = $quality;
		return $this;
	}


	/**
	 * @param string $name
	 * @param array $images
	 * @return \DK\ImagesManager\NamespaceManager
	 */
	public function addList($name, array $images)
	{
		$this->lists[$name] = array(
			'parsed' => null,
			'images' => $images,
		);

		return $this;
	}


	/**
	 * @param string $name
	 * @return \DK\ImagesManager\Image[]
	 * @throws \DK\ImagesManager\InvalidArgumentException
	 */
	public function getList($name)
	{
		if (!isset($this->lists[$name])) {
			throw new InvalidArgumentException('Images list "'. $name. '" is not registered in "'. $this->name. '" namespace.');
		}

		if ($this->lists[$name]['parsed'] === null) {
			$this->lists[$name]['parsed'] = array();

			foreach ($this->lists[$name]['images'] as $image) {
				$this->lists[$name]['parsed'][] = $this->imagesManager->createImage($this->name, new ParsedName($image));
			}
		}

		return $this->lists[$name]['parsed'];
	}

} 