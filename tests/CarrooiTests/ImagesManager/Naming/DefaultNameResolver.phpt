<?php

/**
 * Test: Carrooi\ImagesManager\ImagesManager\Naming\DefaultNameResolver
 *
 * @testCase CarrooiTests\ImagesManager\Naming\DefaultNameResolverTest
 */

namespace CarrooiTests\ImagesManager\Naming;

use Carrooi\ImagesManager\Naming\DefaultNameResolver;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__. '/../../bootstrap.php';

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class DefaultNameResolverTest extends TestCase
{


	public function testGetName()
	{
		$nameResolver = new DefaultNameResolver;

		Assert::same('blue.jpg', $nameResolver->getName('blue.jpg'));
	}


	public function testGetDefaultName()
	{
		$nameResolver = new DefaultNameResolver;

		Assert::null($nameResolver->getDefaultName('blue.jpg'));
	}

}


run(new DefaultNameResolverTest);