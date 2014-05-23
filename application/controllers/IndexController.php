<?php

class IndexController extends Zend_Controller_Action
{
    function indexAction()
    {
    	//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		
		//record test log
		$mod_testlog = new Databases_Tables_TestLog();
		$mod_testlog->log_val= $postStr;
		$mod_testlog->AddLog();
		
		//extract post data
		if (!empty($postStr)){
	
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			$keyword = trim($postObj->Content);
			$time = time();
			$textTpl = "<xml>
						<ToUserName><![CDATA[".$fromUsername."]]></ToUserName>
						<FromUserName><![CDATA[".$toUsername."]]></FromUserName>
						<CreateTime><![CDATA[".$time."]]></CreateTime>
						<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[你好]]></Content>
						</xml>";
			echo $textTpl;
	
		}else {
			echo "";
			exit;
		}
		
		die;
    }
    
    function aboutAction()
    {
    	echo "About Us";
    	die;
    }
    
    function marketingAction()
    {
    	echo "营销活动";
    	die;
    }
    
    function morderAction()
    {
    	echo "点餐页";
    	die;
    }
    
    function marketingSub1Action()
    {
    	echo "每日特价";
    	die;
    }
    
    function marketingSub2Action()
    {
    	echo "厨师推荐";
    	die;
    }
    
    function marketingSub3Action()
    {
    	echo "促销活动";
    	die;
    }
    
    function makeMenuAction()
    {
    	$mod_params = new Databases_Tables_Params();
    	$token = $mod_params->GetVal("WechatToken");
    	
    	$post_uri = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$token;
    	
    	$menu = array(
    		'button' => array(
    			array(
    				'type' => 'view',
    				'name' => 'aaa',
    				'link' => 'http://aaa.com'
    			),
    			array(
    				'type' => 'view',
    				'name' => 'aaa',
    				'link' => 'bbb.com'
    			)
    		)
    	);
    	
    	$menu_string = Zend_Json::encode($menu);
    	
    	$mod_test = new Algorithms_Extensions_Test();
    	$mod_test->http_post_data($post_uri, $menu_string);
    	
    	echo "End.";
    	die;
    }
}

