<?php

/**
 * Test: Carrooi\ImagesManager\ImagesManager\Helpers\Helpers
 *
 * @testCase CarrooiTests\ImagesManager\Helpers\HelpersTest
 */

namespace CarrooiTests\ImagesManager\Helpers;

use Carrooi\ImagesManager\Helpers\Helpers;
use Carrooi\ImagesManager\InvalidArgumentException;
use Nette\Utils\Image as NetteImage;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__. '/../../bootstrap.php';

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class HelpersTest extends TestCase
{


	public function testParseResizeFlags()
	{
		Assert::equal(NetteImage::SHRINK_ONLY | NetteImage::STRETCH, Helpers::parseResizeFlags('shrinkOnly|stretch'));
	}


	public function testParseResizeFlags_single()
	{
		Assert::equal(NetteImage::FIT, Helpers::parseResizeFlags('fit'));
	}


	public function testParseSize_invalidType()
	{
		Assert::exception(function() {
			Helpers::parseSize(new \DateTime);
		}, InvalidArgumentException::class, 'Size must be a string or an integer, object given.');
	}


	public function testParseSize_invalidString()
	{
		Assert::exception(function() {
			Helpers::parseSize('lorem ipsum');
		}, InvalidArgumentException::class, 'Size must be in "<width>x<height>" format.');
	}


	public function testParseSize_int()
	{
		Assert::equal([50, null], Helpers::parseSize(50));
	}


	public function testParseSize_string()
	{
		Assert::equal([50, 20], Helpers::parseSize('50x20'));
	}

}


run(new HelpersTest);