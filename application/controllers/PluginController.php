<?php

class PluginController extends Zend_Controller_Action
{
	function init()
	{
		$this->db = Zend_Registry::get("db");
	}
	
	function viewOrdersAction()
	{
		$mod = new Algorithms_RPC_OrdersServices();
		$mod->FetchNewOrdersServer();
		die;
	}
}