<?php

class Algorithms_Core_BasicServices
{
	var $wechat_signature;
	var $wechat_timestamp;
	var $wechat_nonce;
	
	function __construct(){
		$this->db = Zend_Registry::get("db");
	}
	
	function TurnMsgToObj($post_str)
	{
		if(!empty($post_str))
		{
			$result = simplexml_load_string($post_str, 'SimpleXMLElement', LIBXML_NOCDATA);
		}else{
			$result = NULL;
		}
		
		return $result;
	}
	
	function OperationCenter($post_obj)
	{
		$error = 0;
		
		$this->wechat_signature = $this->wechat_signature;
		$this->wechat_timestamp = $this->wechat_timestamp;
		$this->wechat_nonce = $this->wechat_nonce;
		
		//check visitor permission
		if($this->CheckSignature())
		{
			//check user permission
			$mod_admin = new Databases_Tables_Admin();
			$mod_admin->admin_wechat_openid = $post_obj->FromUserName;
			$admin_info = $mod_admin->IsValid();
			
			if(!empty($admin_info)) //valid
			{
				if('event' == $post_obj->MsgType)
				{
					//proceed
					switch($post_obj->Event)
					{
						case 'CLICK':
							$fromUsername = $post_obj->FromUserName;
							$toUsername = $post_obj->ToUserName;
							$time = time();
							$textTpl = "<xml>
								<ToUserName><![CDATA[".$fromUsername."]]></ToUserName>
								<FromUserName><![CDATA[".$toUsername."]]></FromUserName>
								<CreateTime><![CDATA[".$time."]]></CreateTime>
								<MsgType><![CDATA[text]]></MsgType>
								<Content><![CDATA[成功访问]]></Content>
								</xml>";
							echo $textTpl;
							break;
						default:
							break;
					}
				}else{
					$error = 1004;
				}
			}else{ //invalid
				$error = 1001;
			}
		}else{
			$error = 1002;
		}
		
		return $error;
	}
	
	function CheckSignature()
	{
		echo "sign = ".$this->wechat_signature."<br />";
		echo "timestamp = ".$this->wechat_timestamp."<br />";
		echo "nonce = ".$this->wechat_nonce."<br />";
		die;
		
		$signature = $this->wechat_signature;
		$timestamp = $this->wechat_timestamp;
		$nonce = $this->wechat_nonce;
		
		$mod_params = new Databases_Tables_Params();
		$token = $mod_params->GetVal("WechatToken");
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}