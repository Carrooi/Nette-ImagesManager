<?php

/**
 * Test: Carrooi\ImagesManager\ImagesManager\Helpers\Validators
 *
 * @testCase CarrooiTests\ImagesManager\Helpers\ValidatorTest
 */

namespace CarrooiTests\ImagesManager\Helpers;

use Carrooi\ImagesManager\Helpers\Validators;
use Carrooi\ImagesManager\InvalidImageNameException;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require_once __DIR__. '/../../bootstrap.php';

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class ValidatorsTest extends TestCase
{


	public function testIsImageFullName_true()
	{
		Assert::true(Validators::isImageFullName('blue.jPg'));
	}


	public function testIsImageFullName_false()
	{
		Assert::false(Validators::isImageFullName('blue'));
	}


	public function testValidateImageFullName_true()
	{
		Environment::$checkAssertions = false;

		Validators::validateImageFullName('blue.jpG');
	}


	public function testValidateImageFullName_false()
	{
		Assert::exception(function() {
			Validators::validateImageFullName('blue');
		}, InvalidImageNameException::class, 'Image name must be with valid extension, blue given.');
	}

}


(new ValidatorsTest)->run();
