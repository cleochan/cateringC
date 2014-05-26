<?php

class AjaxController extends Zend_Controller_Action
{
	function init()
	{
		$this->db = Zend_Registry::get("db");
	}
	
	function indexAction()
    {
    	echo "Invaid Action";
			
		die;
    }
    
    function addProductIntoEatinSessionAction()
    {
    	$params = $this->_request->getParams();
    	
    	if($params['pid'])
    	{
    		$mod_orders_info_generation = new Algorithms_Core_OrdersInfoGeneration();
    		
    		if(!$_SESSION['eat-in'])
    		{
    			$mod_orders_info_generation->InitialEatInSession();
    		}
    		
    		$mod_orders_info_generation->item_id = $params['pid'];
    		$result = $mod_orders_info_generation->AddProductIntoEatInSession();
    		
    		echo Zend_Json::encode($result);
    		die;
    	}
    }
}

