<?php

class OrdersController extends Zend_Controller_Action
{
	function init()
	{
		$this->db = Zend_Registry::get("db");
	}
	
	function preDispatch()
	{
		//check identity
		$params = $this->_request->getParams();
		$mod_admin = new Databases_Tables_Admin();
		
		if($params['session_id'])
    	{
    		$_SESSION['admin_session'] = $params['session_id'];
    	}
    	
    	if($_SESSION['admin_session']){
    		$mod_params = new Databases_Tables_Params();
    		if($_SESSION['admin_session'] === $mod_params->GetVal('TestSession'))
    		{
    			$fake_admin = $mod_admin->fetchRow("admin_id=2");
    			$admin_info = $fake_admin->toArray();
    		}else{
    			$admin_info = $mod_admin->CheckSessionValidation();
    		}
    		
    		if(empty($admin_info))
    		{
    			$error = 1005;
    		}else{
    			$_SESSION['admin_info'] = $admin_info;
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
    		$error_msg = $mod_error->AddLog();
    			
    		echo $error_msg;
    		die;
    	}
    	
    	$this->_helper->layout->disableLayout();
	}
	
	function indexAction()
    {
    	echo "Invaid Action";
			
		die;
    }
    
    function placeOrderAction()
    {
    	
    }
    
    function viewStatusAction()
    {
    	
    }
}

