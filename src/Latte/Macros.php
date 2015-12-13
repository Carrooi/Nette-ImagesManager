<?php

namespace Carrooi\ImagesManager\Latte;

use Carrooi\ImagesManager\Helpers\Helpers as UtilsHelpers;
use Carrooi\ImagesManager\ImagesManager;
use Carrooi\ImagesManager\PresetNotExistsException;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class Macros extends MacroSet
{


	public static function install(Compiler $compiler)
	{
		/** @var \Carrooi\ImagesManager\Latte\Macros $me */
		$me = new static($compiler);

		$me->addMacro('image', [$me, 'macroImage']);
		$me->addMacro('src', null, null, [$me, 'macroSrc']);

		$isImage = '$template->getImagesManager()->findImage(%node.args)';

		$me->addMacro('is-image', 'if ('. $isImage. ' !== null) {', '}');
		$me->addMacro('isImage', 'if ('. $isImage. ' !== null) {', '}');

		$me->addMacro('is-not-image', 'if ('. $isImage. ' === null) {', '}');
		$me->addMacro('isNotImage', 'if ('. $isImage. ' === null) {', '}');
	}


	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 * @return string
	 */
	public function macroImage(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write('echo '. get_class($this). '::getUrl($template->getImagesManager(), %node.args);');
	}


	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 * @return string
	 */
	public function macroSrc(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write('echo " src=\"". '. get_class($this). '::getUrl($template->getImagesManager(), %node.args). "\"";');
	}


	/**
	 * @param \Carrooi\ImagesManager\ImagesManager $manager
	 * @param string $namespace
	 * @param string $name
	 * @param int|string|null $size
	 * @param string|null $resizeFlag
	 * @return string
	 */
	public static function getUrl(ImagesManager $manager, $namespace, $name, $size = null, $resizeFlag = null)
	{
		$width = $height = null;

		if ($size !== null) {
			if (!is_int($size) && !preg_match('/^(\d+)x(\d+)$/', $size)) {		// probably preset
				$config = $manager->getConfiguration();

				if (!$config->hasPreset($namespace, $size)) {
					throw new PresetNotExistsException('Preset '. $size. ' does not exists.');
				}

				$preset = $config->getPreset($namespace, $size);
				$width = $preset['width'];
				$height = $preset['height'];
				$resizeFlag = $preset['resizeFlag'];

			} else {
				$size = UtilsHelpers::parseSize($size);
				$width = $size[0];
				$height = $size[1];
			}
		}

		if ($resizeFlag !== null && is_string($resizeFlag)) {
			$resizeFlag = UtilsHelpers::parseResizeFlags($resizeFlag);
		}

		if (!$image = $manager->findImage($namespace, $name)) {
			$dummyImageUrl = $manager->getDummyImageUrl($namespace, $width, $height);

			return $dummyImageUrl ? $dummyImageUrl : '';
		}

		if ($width || $height) {
			$manager->tryStoreThumbnail($image, $width, $height, $resizeFlag);
		}

		return $manager->getUrl($image, $width, $height, $resizeFlag);
	}

}
