<?php

namespace CarrooiTests\Mocks;

use Nette\Application\UI\Control;


/**
 *
 * @author David Kudera <kudera.d@gmail.com>
 */
class ControlMock extends Control
{


	/** @var \CarrooiTests\Mocks\PresenterMock */
	private $_presenter;


	/**
	 * @param bool $need
	 * @return \CarrooiTests\Mocks\PresenterMock
	 */
	public function getPresenter($need = true)
	{
		if (!$this->_presenter) {
			$this->_presenter = new PresenterMock;
		}

		return $this->_presenter;
	}

}
