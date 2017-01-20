<?php

namespace CarrooiTests\Mocks;

use Nette\Application\UI\Presenter;
use Nette\Http\Response;

/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class PresenterMock extends Presenter
{


	/** @var \Nette\Http\Response */
	private $_httpResponse;


	/**
	 * @return \Nette\Http\Response
	 */
	public function getHttpResponse()
	{
		if (!$this->_httpResponse) {
			$this->_httpResponse = new Response;
		}

		return $this->_httpResponse;
	}

}
