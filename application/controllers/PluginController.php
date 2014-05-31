<?php

class PluginController extends Zend_Controller_Action
{
	function init()
	{
		$this->db = Zend_Registry::get("db");
	}
	
	function viewOrdersAction()
	{
		$mod = new Algorithms_RPC_OrdersServices();
		$mod->FetchNewOrdersServer();
		die;
	}
	
	function test1Action()
	{
		$redirect_uri = urlencode("http://wechat.jushulin.mobi/plugin/test-target");
		
		$this->_redirect("https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx8e7db22a86a79ad8&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_base&state=123#wechat_redirect");
		die;
	}
	
	function test2Action()
	{
		$this->_redirect("https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxf0e81c3bee622d60&redirect_uri=http%3A%2F%2Fnba.bluewebgame.com%2Foauth_response.php&response_type=code&scope=snsapi_userinfo&state=something123#wechat_redirect");
		die;
	}
	
	function testTargetAction()
	{
		$params = $this->_request->getParams();
		
		var_dump($params);
		die;
	}
}