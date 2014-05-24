<?php

class IndexController extends Zend_Controller_Action
{
    function indexAction()
    {
    	//get posted data
    	$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
    	$signature = $_GET["signature"];
    	$timestamp = $_GET["timestamp"];
    	$nonce = $_GET["nonce"];
    	
    	//initial for error log
    	$ip = NULL;
    	$note1 = NULL;
    	$note2 = NULL;
		
		//record test log
		$mod_testlog = new Databases_Tables_TestLog();
		$mod_testlog->log_val= $postStr;
		$mod_testlog->AddLog();
		
		//load basic service class
		$mod_basic_services = new Algorithms_Core_BasicServices();
		
		//check error 1002
		$mod_basic_services->wechat_signature = $_GET["signature"];
		$mod_basic_services->wechat_timestamp = $_GET["timestamp"];
		$mod_basic_services->wechat_nonce = $_GET["nonce"];
		if($mod_basic_services->CheckSignature())
		{
			//extract post data
			if (!empty($postStr)){
				$postObj = $mod_basic_services->TurnMsgToObj($postStr);
				$erorr = $mod_basic_services->OperationCenter($postObj);
			}else {
				$error = 1003;
			}
		}else{
			$error = 1002;
		}
		
		//register error
		if($error)
		{
			$mod_error = new Databases_Tables_ErrorLog();
			$mod_error->error_id = $error;
			$mod_error->visitor_ip = $_SERVER['REMOTE_ADDR'];
			if($note1){$mod_error->note1 = $note1;}
			if($note2){$mod_error->note2 = $note2;}
			$error_msg = $mod_error->AddLog();
			
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			$time = time();
			$textTpl = "<xml>
						<ToUserName><![CDATA[".$fromUsername."]]></ToUserName>
						<FromUserName><![CDATA[".$toUsername."]]></FromUserName>
						<CreateTime><![CDATA[".$time."]]></CreateTime>
						<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[".$error_msg."]]></Content>
						</xml>";
			echo $textTpl;
		}
			
		die;
    }
}

