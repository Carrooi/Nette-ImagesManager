<?php

namespace Carrooi\ImagesManager\Latte;

use Carrooi\ImagesManager\Helpers as ImagesHelpers;
use Carrooi\ImagesManager\ImageNotExistsException;
use Carrooi\ImagesManager\ImagesManager;
use Latte\Macros\MacroSet;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\PhpWriter;

/**
 *
 * @author David Kudera
 */
class Macros extends MacroSet
{


	/**
	 * @param \Latte\Compiler $compiler
	 */
	public static function install(Compiler $compiler)
	{
		$me = new static($compiler);		/** @var $me \Carrooi\ImagesManager\Latte\Macros */

		$me->addMacro('image', array($me, 'macroImage'));
		$me->addMacro('src', null, null, array($me, 'macroSrc'));

		$me->addMacro('isImage', array($me, 'macroIsImage'), '}');
		$me->addMacro('isNotImage', array($me, 'macroIsImage'), '}');
	}


	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 * @return string
	 */
	public function macroImage(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write("echo \\Carrooi\\ImagesManager\\Latte\\Macros::getUrl(\$template->getImagesManager(), %node.args);");
	}


	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 * @return string
	 */
	public function macroSrc(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write("echo ' src=\"'. \\Carrooi\\ImagesManager\\Latte\\Macros::getUrl(\$template->getImagesManager(), %node.args). '\"';");
	}


	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 * @return string
	 */
	public function macroIsImage(MacroNode $node, PhpWriter $writer)
	{
		$not = $node->name === 'isNotImage' ? '!' : '';
		$code = "if ($not\$template->getImagesManager()->createImage(%node.args)->isExists()) {";

		return $writer->write($code);
	}


	/**
	 * @param \Carrooi\ImagesManager\ImagesManager $imagesManager
	 * @return string
	 */
	public static function getUrl(ImagesManager $imagesManager)
	{
		$args = func_get_args();
		array_shift($args);

		try {
			$absolute = false;
			if (strpos($args[1], '//') === 0) {
				$absolute = true;
				$args[1] = substr($args[1], 2);
			}

			/** @var \Carrooi\ImagesManager\Image $image */
			$image = call_user_func_array(array($imagesManager, 'load'), $args);

			return $image->getUrl($absolute);
		} catch (ImageNotExistsException $e) {
			if (count($args) > 2) {
				$size = ImagesHelpers::parseSize($args[2]);
				return 'http://satyr.io/'. $size->width. 'x'. ($size->height ? $size->height : $size->width);
			}
		}

		return '';
	}

}
