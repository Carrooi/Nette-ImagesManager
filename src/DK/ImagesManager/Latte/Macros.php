<?php

namespace DK\ImagesManager\Latte;

use DK\ImagesManager\ImageNotExistsException;
use DK\ImagesManager\ImagesManager;
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
		$me = new static($compiler);		/** @var $me \DK\ImagesManager\Latte\Macros */

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
		return $writer->write("echo \\DK\\ImagesManager\\Latte\\Macros::getUrl(\$template->getImagesManager(), %node.args);");
	}


	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 * @return string
	 */
	public function macroSrc(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write("echo ' src=\"'. \\DK\\ImagesManager\\Latte\\Macros::getUrl(\$template->getImagesManager(), %node.args). '\"';");
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
	 * @param \DK\ImagesManager\ImagesManager $imagesManager
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

			/** @var \DK\ImagesManager\Image $image */
			$image = call_user_func_array(array($imagesManager, 'load'), $args);

			return $image->getUrl($absolute);
		} catch (ImageNotExistsException $e) {}

		return '';
	}

}
 