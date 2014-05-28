<?php

class PluginController extends Zend_Controller_Action
{
	function init()
	{
		$this->db = Zend_Registry::get("db");
	}
	
	function postTestAction()
	{
		$textTpl = "<xml>
						<ToUserName><![CDATA[MARK]]></ToUserName>
						<FromUserName><![CDATA[123]]></FromUserName>
					</xml>";
		
		echo $textTpl;
		
		die;
	}
}