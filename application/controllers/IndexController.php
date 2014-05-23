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
}

