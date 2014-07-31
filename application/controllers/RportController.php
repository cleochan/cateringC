<?php

class RportController extends Zend_Controller_Action
{
	function indexAction()
	{
		require_once 'PHPRPC/phprpc_server.php';
    	
    	$a = new PHPRPC_Server();
    	$a->add("FetchNewOrdersServer", new Algorithms_RPC_OrdersServices());
    	$a->add("UpdateLogSync", new Algorithms_RPC_OrdersServices());
    	$a->add("LogSyncDown", new Algorithms_RPC_OrdersServices());
    	$a->start();
    	
    	die;
	}
}