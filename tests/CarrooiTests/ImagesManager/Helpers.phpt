<?php

/**
 * Test: Carrooi\ImagesManager\Helpers
 *
 * @testCase CarrooiTests\ImagesManager\Helpers
 * @author David Kudera
 */

namespace CarrooiTests\ImagesManager;

require_once __DIR__. '/../bootstrap.php';

use Tester\TestCase;
use Tester\Assert;
use Carrooi\ImagesManager\Helpers;

/**
 *
 * @author David Kudera
 */
class HelpersTest extends TestCase
{


	public function testGetExtension()
	{
		Assert::same('jpg', Helpers::getExtension('/var/www/images/car.jpg'));
		Assert::same('jpeg', Helpers::getExtension('/var/www/images/car.jpeg'));
		Assert::same('gif', Helpers::getExtension('/var/www/images/car.GIF'));
	}


	public function testParseName()
	{
		$name = Helpers::parseName('myCar.jpg');

		Assert::equal(array(
			'name' => 'myCar',
			'extension' => 'jpg',
		), (array) $name);
	}


	public function testParseName_string_invalid()
	{
		Assert::exception(function() {
			Helpers::parseName('car_my_jpg');
		}, 'Carrooi\ImagesManager\InvalidArgumentException', 'Name must in "<name>.<extension>" format, "car_my_jpg" given.');
	}


	public function testParseName_string_invalid_name()
	{
		Assert::exception(function() {
			Helpers::parseName('car_my.jpg');
		}, 'Carrooi\ImagesManager\InvalidArgumentException', 'Name must in "<name>.<extension>" format, where <name> must be alphanumerical. "car_my.jpg" given.');
	}


	public function testParseName_object()
	{
		Assert::exception(function() {
			Helpers::parseName(new \stdClass);
		}, 'Carrooi\ImagesManager\InvalidArgumentException', 'Name must be a string, object given.');
	}


	public function testParseSize_int()
	{
		$size = Helpers::parseSize(50);

		Assert::equal(array(
			'width' => 50,
			'height' => null,
		), (array) $size);
	}


	public function testParseSize_string()
	{
		$size = Helpers::parseSize('20x100');

		Assert::equal(array(
			'width' => 20,
			'height' => 100,
		), (array) $size);
	}


	public function testParseSize_object()
	{
		Assert::exception(function() {
			Helpers::parseSize(new \stdClass);
		}, 'Carrooi\ImagesManager\InvalidArgumentException', 'Size must be a string or an integer, object given.');
	}


	public function testParseSize_string_invalid()
	{
		Assert::exception(function() {
			Helpers::parseSize('test string');
		}, 'Carrooi\ImagesManager\InvalidArgumentException', 'Size must be in "<width>x<height>" format.');
	}


	public function testParseFileName()
	{
		$fileName = '/var/www/images/dots/dots_black_fit_2.jpg';
		$mask = 'dots_black_<resizeFlag>_<size>.jpg';

		Assert::equal(array(
			'resizeFlag' => 'fit',
			'size' => 2,
		), (array) Helpers::parseFileName($fileName, $mask));
	}


	public function testParseFileName_full_size()
	{
		$fileName = '/var/www/images/dots/dots_black_fit_20x50.jpg';
		$mask = 'dots_black_<resizeFlag>_<size>.jpg';

		Assert::equal(array(
			'resizeFlag' => 'fit',
			'size' => '20x50',
		), (array) Helpers::parseFileName($fileName, $mask));
	}


	public function testParseFileName_invalid()
	{
		$fileName = '/var/www/images/dots/dots_black_fit.jpg';
		$mask = 'dots_black_<resizeFlag>_<size>.jpg';

		Assert::null(Helpers::parseFileName($fileName, $mask));
	}

}


run(new HelpersTest);
