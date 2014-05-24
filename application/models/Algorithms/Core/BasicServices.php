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
							
							//public params
							$fromUsername = $post_obj->FromUserName;
							$toUsername = $post_obj->ToUserName;
							$time = time();
							
							if('PLACE_ORDER' == $post_obj->EventKey)
							{
								$url = "http://wechat.jushulin.mobi/orders/place-order";
								
								$textTpl = "<xml>
									<ToUserName><![CDATA[".$fromUsername."]]></ToUserName>
									<FromUserName><![CDATA[".$toUsername."]]></FromUserName>
									<CreateTime><![CDATA[".$time."]]></CreateTime>
									<MsgType><![CDATA[news]]></MsgType>
									<ArticleCount>1</ArticleCount>
									<Articles>
									<item>
									<Title><![CDATA[Place Order]]></Title>
									<Description><![CDATA[用户陈鹏进入点餐系统]]></Description>
									<Url><![CDATA[".$url."]]></Url>
									</item>
									</Articles>
									</xml>";
							}elseif('VIEW_STATUS' == $post_obj->EventKey){
								$content = "VIEW_STATUS";
							}
							
// 							$textTpl = "<xml>
// 								<ToUserName><![CDATA[".$fromUsername."]]></ToUserName>
// 								<FromUserName><![CDATA[".$toUsername."]]></FromUserName>
// 								<CreateTime><![CDATA[".$time."]]></CreateTime>
// 								<MsgType><![CDATA[text]]></MsgType>
// 								<Content><![CDATA[".$content."]]></Content>
// 								</xml>";
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