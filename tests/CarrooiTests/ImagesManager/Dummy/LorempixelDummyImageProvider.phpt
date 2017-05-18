<?php

/**
 * Test: Carrooi\ImagesManager\ImagesManager\Dummy\LorempixelDummyImageProvider
 *
 * @testCase CarrooiTests\ImagesManager\Dummy\LorempixelDummyImageProviderTest
 */

namespace CarrooiTests\ImagesManager\Dummy;

use Carrooi\ImagesManager\Dummy\LorempixelDummyImageProvider;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__. '/../../bootstrap.php';

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class LorempixelDummyImageProviderTest extends TestCase
{


	public function testGetUrl()
	{
		$dummy = new LorempixelDummyImageProvider;

		Assert::same('http://lorempixel.com/50/100/', $dummy->getUrl(50, 100));
	}


	public function testGetUrl_category()
	{
		$dummy = new LorempixelDummyImageProvider;

		Assert::same('http://lorempixel.com/50/100/cats/', $dummy->getUrl(50, 100, 'cats'));
	}

}


(new LorempixelDummyImageProviderTest)->run();
