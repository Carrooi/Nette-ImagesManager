<?php

namespace DK\ImagesManager\Latte;

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
		$code = "\$__imageUrlTmp__ = ''; try { \$__imageUrlTmp__ = \$template->getImagesManager()->load(%node.args)->getUrl(); } catch (\\DK\\ImagesManager\\ImageNotExistsException \$e) {}";

		return $writer->write("$code echo \$__imageUrlTmp__;");
	}


	/**
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 * @return string
	 */
	public function macroSrc(MacroNode $node, PhpWriter $writer)
	{
		$code = "\$__imageUrlTmp__ = ''; try { \$__imageUrlTmp__ = \$template->getImagesManager()->load(%node.args)->getUrl(); } catch (\\DK\\ImagesManager\\ImageNotExistsException \$e) {}";

		return $writer->write("$code echo ' src=\"'. \$__imageUrlTmp__. '\"';");
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

}
 