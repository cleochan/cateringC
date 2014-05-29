<?php

class Databases_Tables_Orders extends Zend_Db_Table
{
    protected $_name = 'orders';
    var $orders_id;
    var $orders_channel;
    var $orders_code;
    var $orders_time;
    var $orders_status;
    var $orders_payment_status;
    var $orders_type;
    var $orders_amount;
    var $orders_cash;
    var $orders_change;
    var $orders_subtotal;
    var $orders_coupon;
    var $orders_discount;
    var $orders_member_id;
    var $table_id;
    var $users_id;
    var $device_id;
    var $orders_items; //array
    var $take_out_phone;
    
    function InsertOrder()
    {
    	$result = 0;
    	
    	if($this->orders_channel && $this->orders_type && $this->orders_items)
    	{
    		$row = $this->createRow();
    		$row->orders_channel = $this->orders_channel;
    		$row->orders_time = $this->orders_time;
    		$row->orders_status = $this->orders_status;
    		$row->orders_payment_status = $this->orders_payment_status;
    		$row->orders_type = $this->orders_type;
    		$row->orders_amount = $this->orders_amount;
    		$row->orders_cash = $this->orders_cash;
    		$row->orders_change = $this->orders_change;
    		$row->orders_subtotal = $this->orders_subtotal;
    		$row->orders_coupon = $this->orders_coupon;
    		$row->orders_discount = $this->orders_discount;
    		$row->orders_member_id = $this->orders_member_id;
    		$row->table_id = $this->table_id;
    		$row->users_id = $this->users_id;
    		$orders_id = $row->save();
    		
    		//insert items
    		if($orders_id)
    		{
    			$mod_orders_contains = new Databases_Tables_OrdersContains();
    			$mod_orders_contains->orders_id = $orders_id;
    			$mod_orders_contains->items_array = $this->orders_items;
    			$result = $mod_orders_contains->InsertItems();
    		}
    	}
    	
    	return $orders_id;
    }
    
    function UpdateStatus()
    {
    	$result = 0;
    	
    	if($this->orders_id && $this->orders_status)
    	{
    		$row = $this->fetchRow("orders_id='".$this->orders_id."'");
    		$row->orders_status = $this->orders_status;
    		$result = $row->save();
    	}
    	
    	return $result;
    }
}
