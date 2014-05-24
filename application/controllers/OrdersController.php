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
    		$mod_admin->session_id = $params['session_id'];
    		if(!$mod_admin->CheckInitialValidation())
    		{
    			$error = 1002;
    		}
    	}elseif($_SESSION['admin_session']){
    		if(!$mod_admin->CheckCurrentSessionValidation())
    		{
    			$error = 1005;
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
	}
	
	function indexAction()
    {
    	echo "Invaid Operation";
			
		die;
    }
    
    function placeOrderAction()
    {
    	$params = $this->_request->getParams();
    	
    }
    
    function viewStatusAction()
    {
    	echo "View Status Page.";
    		
    	die;
    }
}

