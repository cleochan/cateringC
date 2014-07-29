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
    
    function addSetsIntoEatinSessionAction()
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
    		$result = $mod_orders_info_generation->AddSetsIntoEatInSession();
    		
    		echo Zend_Json::encode($result);
    		die;
    	}
    }
    
    function addProductIntoUpdateOrderSessionAction()
    {
    	$params = $this->_request->getParams();
    	
    	if($params['pid'])
    	{
    		$mod_orders_info_generation = new Algorithms_Core_OrdersInfoGeneration();
    		
    		if(!$_SESSION['update-order'])
    		{
    			$mod_orders_info_generation->InitialUpdateOrderSession();
    		}
    		
    		$mod_orders_info_generation->item_id = $params['pid'];
    		$result = $mod_orders_info_generation->AddProductIntoUpdateOrderSession();
    		
    		echo Zend_Json::encode($result);
    		die;
    	}
    }
    
    function addSetsIntoUpdateOrderSessionAction()
    {
    	$params = $this->_request->getParams();
    	
    	if($params['pid'])
    	{
    		$mod_orders_info_generation = new Algorithms_Core_OrdersInfoGeneration();
    		
    		if(!$_SESSION['update-order'])
    		{
    			$mod_orders_info_generation->InitialUpdateOrderSession();
    		}
    		
    		$mod_orders_info_generation->item_id = $params['pid'];
    		$result = $mod_orders_info_generation->AddSetsIntoUpdateOrderSession();
    		
    		echo Zend_Json::encode($result);
    		die;
    	}
    }
}

