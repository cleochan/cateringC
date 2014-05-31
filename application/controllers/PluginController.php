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
		$redirect_uri = urlencode("http://wechat.jushulin.mobi/plugin/test3");
		
		$this->_redirect("https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx8e7db22a86a79ad8&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_base&state=123#wechat_redirect");
		die;
	}
	
	function test2Action()
	{
		$this->_redirect("https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxf0e81c3bee622d60&redirect_uri=http%3A%2F%2Fnba.bluewebgame.com%2Foauth_response.php&response_type=code&scope=snsapi_userinfo&state=something123#wechat_redirect");
		die;
	}
	
	function test3Action()
	{
		$params = $this->_request->getParams();
		
		$ch = curl_init();
		
		$str ='https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx8e7db22a86a79ad8&secret=eb47e51ca5e3bc2b880133fa21d87696&code='.$params['code'].'&grant_type=authorization_code';
		curl_setopt($ch, CURLOPT_URL, $str);
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		$output = curl_exec($ch);
		
		$result = Zend_Json::decode($output);
		
		if('oigx2uF2eZLp3sPxp3V8Nco-3q2M' == $result['openid'])
		{
			if('oigx2uF2eZLp3sPxp3V8Nco-3q2M' == $_SESSION['openid'])
			{
				echo "Valid: Existed User.";
			}else{
				echo "Valid: New User.";
				$_SESSION['openid'] = $result['openid'];
			}
		}else{
			echo "Invalid visits.";
		}
		
		die;
	}
}