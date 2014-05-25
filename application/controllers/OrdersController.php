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
	}
	
	function indexAction()
    {
    	echo "Invaid Action";
			
		die;
    }
    
    function placeOrderAction()
    {
    	//Fetch category names
    	$get_categories = new Databases_Tables_MateriaCategories();
    	$this->view->categories_array = $categories_array = $get_categories->CategoriesForOrders();
    	
    	//Fetch product and category relations
    	$products = new Databases_Tables_MateriaProducts();
    	$products->business_channel_id = 1;
    	$products->category_id_array = $categories_array['normal'];
    	$this->view->product_array = $products->FetchProductsByCategory();
    	 
    	//Fetch sets and category relations
    	$sets = new Databases_Tables_MateriaSets();
    	$sets->business_channel_id = 1;
    	$sets->category_id_array = $categories_array['sets'];
    	$this->view->sets_array = $sets->FetchProductsByCategory();
    }
    
    function viewStatusAction()
    {
    	
    }
    
    function test1Action()
    {
    	$params = $this->_request->getParams();
    	
    	$a = array("msg"=>$params['pid']);
    	
    	echo Zend_Json::encode($a);
    	die;
    }
}

