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
    	$post_uri = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=iN0uomjBy9DAyawFcFJ3HbtkiaJlPpP-RXLCSqwCjKhJxRbNpns3A64kYc7XGjOZZlMKH8V2oUXSuj-W4FgsjQ";
    	
    	$menu = array(
    		'button' => array(
    			array(
    				'type' => 'view',
    				'name' => '点餐Go',
    				'url' => 'http://wechat.jushulin.mobi/index/morder'
    			),
    			array(
    				'type' => 'view',
    				'name' => '关于我们',
    				'url' => 'http://wechat.jushulin.mobi/index/about'
    			),
    			array(
    				'name' => '营销活动',
    				'sub_button' => array(
    						array(
    								'type' => 'view',
    								'name' => '每日特价',
    								'url' => 'http://wechat.jushulin.mobi/index/marketing-sub1'
    						),
    						array(
    								'type' => 'view',
    								'name' => '厨师推荐',
    								'url' => 'http://wechat.jushulin.mobi/index/marketing-sub2'
    						),
    						array(
    								'type' => 'view',
    								'name' => '促销活动',
    								'url' => 'http://wechat.jushulin.mobi/index/marketing-sub3'
    						)
    				)
    			)
    		)
    	);
    	
    	$menu_string = Zend_Json::encode($menu);
    	echo $menu_string;die;
    	$mod_test = new Algorithms_Extensions_Test();
    	$mod_test->http_post_data($post_uri, $menu_string);
    	
    	echo "End.";
    	die;
    }
}

