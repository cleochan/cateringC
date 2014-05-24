<?php

class OrdersController extends Zend_Controller_Action
{
    function indexAction()
    {
    	echo "Invaid Operation";
			
		die;
    }
    
    function placeOrderAction()
    {
    	$params = $this->_request->getParams();
    	
    	$this->view->session_id = $params['session_id'];
    }
    
    function viewStatusAction()
    {
    	echo "View Status Page.";
    		
    	die;
    }
}

