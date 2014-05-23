<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initAutoload()
	{
		$sessionConfig = new Zend_Config_Ini('../application/configs/session.ini', 'development');
		Zend_Session::setOptions($sessionConfig->toArray());
		
		Zend_Session::start();
	}
}

