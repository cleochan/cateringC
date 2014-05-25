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
    var $device_id;
    var $orders_items; //array
    var $take_out_phone;
    
    function InsertOrder()
    {
    	$result = 0;
    	
    	if($this->orders_channel && $this->orders_type && $this->orders_items)
    	{
    		//generate order code
    		$order_code = $this->GenerateOrderCode();
    		
    		if($order_code)
    		{
    			$row = $this->createRow();
    			$row->orders_channel = $this->orders_channel;
    			$row->orders_code = $order_code;
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
    			$row->users_id = $_SESSION['user_info']['users_id'];
    			$row->device_id = $this->device_id;
    			$orders_id = $row->save();
    			
    			//insert items
    			if($orders_id)
    			{
    				$mod_orders_contains = new Databases_Tables_OrdersContains();
    				$mod_orders_contains->orders_id = $orders_id;
    				$mod_orders_contains->items_array = $this->orders_items;
    				$result = $mod_orders_contains->InsertItems();
    				
    				//add accounting log
    				$mod_accounting_log = new Databases_Tables_AccountingLog();
    				$mod_accounting_log->device_id = $_SESSION['client_info']['client_id'];
    				$mod_accounting_log->user_id = $_SESSION['user_info']['users_id'];
    				$mod_accounting_log->action_category = 1; //CREATE ORDER
    				$mod_accounting_log->action_value = $this->orders_amount;
    				$mod_accounting_log->action_note_1 =$order_code;
    				$mod_accounting_log-> MakePlusAction();
    				
    				//add delivery info
    				if($this->take_out_phone)
    				{
    					$mod_order_add = new Databases_Tables_OrdersAddresses();
    					
    					if('new' == $this->take_out_phone['add_type'])
    					{
    						$mod_order_add->addresses_id = $this->take_out_phone['midselect'];
    						$mod_order_add->address_details = $this->take_out_phone['add_details'];
    						$mod_order_add->phone = $this->take_out_phone['phone_num'];
    						$mod_order_add->delivery_notes = $this->take_out_phone['order_reminders'];
    						
    						//将当前地址加入会员地址簿
    						$mod_members_add = new Databases_Tables_MembersAddresses();
    						$mod_members_add->members_id = $this->orders_member_id;
    						$mod_members_add->addresses_id = $this->take_out_phone['midselect'];
    						$mod_members_add->address_details = $this->take_out_phone['add_details'];
    						$mod_members_add->phone = $this->take_out_phone['phone_num'];
    						$mod_members_add->delivery_notes = $this->take_out_phone['order_reminders'];
    						$mod_members_add->CreateRecord();
    					}else{
    						$mod_members_add = new Databases_Tables_MembersAddresses();
    						$mod_members_add->members_addresses_id = $this->take_out_phone['add_type'];
    						$member_add_info = $mod_members_add->FetchMemberAddInfo();
    						if(!empty($member_add_info))
    						{
    							$mod_order_add->addresses_id = $member_add_info['addresses_id'];
    							$mod_order_add->address_details = $member_add_info['address_details'];
    							$mod_order_add->phone = $member_add_info['phone'];
    							if($this->take_out_phone['order_reminders'])
    							{
    								$mod_order_add->delivery_notes = $this->take_out_phone['order_reminders'];
    							}else{
    								$mod_order_add->delivery_notes = $member_add_info['delivery_notes'];
    							}
    						}
    					}
    					
    					$mod_order_add->orders_id = $orders_id;
    					$mod_order_add->InsertAddress();
    				}
    			}
    		}
    	}
    	
    	return $orders_id;
    }
    
    function GenerateOrderCode()
    {
    	$result = 0;
    	
    	if($this->orders_channel)
    	{
    		$mod_params = new Databases_Tables_Params();
    		$terminal_id = $mod_params->FetchTerminalId();
    		
    		$mod_channels = new Databases_Tables_BusinessChannels();
    		$mod_channels->buisness_channel_id = $this->orders_channel;
    		$prefix = $mod_channels->FetchOrdersPrefix();
    		
    		if($terminal_id && $prefix)
    		{
    			$result = $prefix.$terminal_id.time();
    		}
    	}
    	
    	return $result;
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
