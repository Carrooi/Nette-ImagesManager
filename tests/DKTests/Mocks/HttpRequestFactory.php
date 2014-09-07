<?php

namespace DKTests\Mocks;

use Nette\Http\RequestFactory;

/**
 *
 * @author David Kudera
 */
class HttpRequestFactory extends RequestFactory
{


	/**
	 * @return \Nette\Http\Request
	 */
	public function createHttpRequest()
	{
		$request = parent::createHttpRequest();

		$request->getUrl()->setHost('localhost');

		return $request;
	}


}
 