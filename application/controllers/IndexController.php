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
				$error = $mod_basic_services->OperationCenter($postObj);
			}else {
				$error = 1003;
			}
		}else{
			$error = 1002;
		}
		
		//register error
// 		if($error)
// 		{
// 			$mod_error = new Databases_Tables_ErrorLog();
// 			$mod_error->error_id = $error;
// 			$mod_error->visitor_ip = $_SERVER['REMOTE_ADDR'];
// 			if($note1){$mod_error->note1 = $note1;}
// 			if($note2){$mod_error->note2 = $note2;}
// 			$error_msg = $mod_error->AddLog();
			
// 			$fromUsername = $postObj->FromUserName;
// 			$toUsername = $postObj->ToUserName;
// 			$time = time();
// 			$textTpl = "<xml>
// 						<ToUserName><![CDATA[".$fromUsername."]]></ToUserName>
// 						<FromUserName><![CDATA[".$toUsername."]]></FromUserName>
// 						<CreateTime><![CDATA[".$time."]]></CreateTime>
// 						<MsgType><![CDATA[text]]></MsgType>
// 						<Content><![CDATA[".$error_msg."]]></Content>
// 						</xml>";
// 			echo $textTpl;
// 		}
			
		die;
    }
    
    function authPlaceOrderAction()
	{
		$mod_params = new Databases_Tables_Params();
		$app_id = $mod_params->GetVal('AppIdYX');
		
		$redirect_uri = urlencode("http://wechat.jushulin.mobi/index/auth-place-order-submit");
		
		$this->_redirect("https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$app_id."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_base&state=123#wechat_redirect");
		die;
	}
	
	function authPlaceOrderSubmitAction()
	{
		$params = $this->_request->getParams();
		
		$mod_params = new Databases_Tables_Params();
		$app_id = $mod_params->GetVal('AppIdYX');
		$app_secret = $mod_params->GetVal('AppSecretYX');
		
		$ch = curl_init();
		
		$str ='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$app_id.'&secret='.$app_secret.'&code='.$params['code'].'&grant_type=authorization_code';
		curl_setopt($ch, CURLOPT_URL, $str);
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		$output = curl_exec($ch);
		
		$result = Zend_Json::decode($output);
		
		$mod_admin = new Databases_Tables_Admin();
		$mod_admin->openid_yx = $result['openid'];
		if($mod_admin->CheckYxOpenIdValidation())
		{
			$this->_redirect("/orders/place-order");
		}else{
			echo "<font size='80'>对不起，系统不对外开放。<br />".substr($result['openid'],-5)."</font>";
			$mod_yx_tried_openid_log = new Databases_Tables_YxTriedOpenidLog();
			$mod_yx_tried_openid_log->AddLog($result['openid']);
		}
		
		die;
	}
    
    function authViewStatusAction()
	{
		$mod_params = new Databases_Tables_Params();
		$app_id = $mod_params->GetVal('AppIdYX');
		
		$redirect_uri = urlencode("http://wechat.jushulin.mobi/index/auth-view-status-submit");
		
		$this->_redirect("https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$app_id."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_base&state=123#wechat_redirect");
		die;
	}
	
	function authViewStatusSubmitAction()
	{
		$params = $this->_request->getParams(); //
		
		$mod_params = new Databases_Tables_Params();
		$app_id = $mod_params->GetVal('AppIdYX');
		$app_secret = $mod_params->GetVal('AppSecretYX');
		
		$ch = curl_init();
		
		$str ='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$app_id.'&secret='.$app_secret.'&code='.$params['code'].'&grant_type=authorization_code';
		curl_setopt($ch, CURLOPT_URL, $str);
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		$output = curl_exec($ch);
		
		$result = Zend_Json::decode($output);
		
		$mod_admin = new Databases_Tables_Admin();
		$mod_admin->openid_yx = $result['openid'];
		if($mod_admin->CheckYxOpenIdValidation())
		{
			$this->_redirect("/orders/view-status");
		}else{
			echo "<font size='80'>对不起，系统不对外开放。<br />".substr($result['openid'],-5)."</font>";
			$mod_yx_tried_openid_log = new Databases_Tables_YxTriedOpenidLog();
			$mod_yx_tried_openid_log->AddLog($result['openid']);
		}
		
		die;
	}
	
    function authViewChangesAction()
	{
		$mod_params = new Databases_Tables_Params();
		$app_id = $mod_params->GetVal('AppIdYX');
		
		$redirect_uri = urlencode("http://wechat.jushulin.mobi/index/auth-view-changes-submit");
		
		$this->_redirect("https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$app_id."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_base&state=123#wechat_redirect");
		die;
	}
	
	function authViewChangesSubmitAction()
	{
		$params = $this->_request->getParams(); //
		
		$mod_params = new Databases_Tables_Params();
		$app_id = $mod_params->GetVal('AppIdYX');
		$app_secret = $mod_params->GetVal('AppSecretYX');
		
		$ch = curl_init();
		
		$str ='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$app_id.'&secret='.$app_secret.'&code='.$params['code'].'&grant_type=authorization_code';
		curl_setopt($ch, CURLOPT_URL, $str);
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		$output = curl_exec($ch);
		
		$result = Zend_Json::decode($output);
		
		$mod_admin = new Databases_Tables_Admin();
		$mod_admin->openid_yx = $result['openid'];
		if($mod_admin->CheckYxOpenIdValidation())
		{
			$this->_redirect("/orders/view-changes");
		}else{
			echo "<font size='80'>对不起，系统不对外开放。<br />".substr($result['openid'],-5)."</font>";
			$mod_yx_tried_openid_log = new Databases_Tables_YxTriedOpenidLog();
			$mod_yx_tried_openid_log->AddLog($result['openid']);
		}
		
		die;
	}
}

