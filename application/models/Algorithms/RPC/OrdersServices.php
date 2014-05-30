<?php
class Algorithms_RPC_OrdersServices
{	
	function __construct(){
    	$this->db = Zend_Registry::get("db");
    }
    
    function FetchNewOrdersServer()
    {
    	$result = array();
    	
    	$data = $this->db->select();
    	$data->from("orders", array("orders_id"));
    	$data->where("orders_status = ?", 1);
    	$data->where("orders_time >= ?", date("Y-m-d")." 00:00:00");
    	
    	$rows = $this->db->fetchAll($data);
    	
    	$order_id_array = array();
    	
    	if(!empty($rows))
    	{
    		foreach($rows as $row)
    		{
    			$order_id_array[] = $row['orders_id'];
    		}
    	}
    	
    	if(!empty($order_id_array))
    	{
    		$mod_order_generation = new Algorithms_Core_OrdersInfoGeneration();
    		
    		foreach($order_id_array as $order_id)
    		{
    			$mod_order_generation->order_id = $order_id;
    			$order_info = $mod_order_generation->MakeOrderArrayLikeSession();
    			
    			$result[$order_id] = Zend_Json::encode($order_info);
    		}
    		
    		//update order status
    		$mod_orders= new Databases_Tables_Orders();
    		$set = array("orders_status"=>2); //Sent
    		$where = $this->db->quoteInto("orders_id IN (?)", $order_id_array);
    		$mod_orders->update($set, $where);
    	}
    	
    	return $result;
    }
    
    function UpdateLogSync($post_array)
    {
    	$result = 0;
    	
    	if(!empty($post_array))
    	{
    		$mod_log_sync = new Databases_Tables_LogSync();
    		$event_array = array();
    		
    		//data restructure
    		foreach($post_array as $pa)
    		{
    			// add sync log
    			$mod_log_sync->log_time = $pa['log_time'];
    			$mod_log_sync->log_event = $pa['log_event'];
    			$mod_log_sync->log_key = $pa['log_key'];
    			if($pa['log_val'])
    			{
    				$mod_log_sync->log_val = $pa['log_val'];
    			}
    			$mod_log_sync->AddLog();
    			
    			//combine array
    			if(!$event_array[$pa['log_event']])
    			{
    				$event_array[$pa['log_event']] = array();
    			}
    			
    			$event_array[$pa['log_event']][$pa['log_key']] = $pa['log_val'];
    		}
    		
    		//proceed
    		
    		/**
    		 * UPDATE_ORDER_STATUS
    		 */
    		if(!empty($event_array['UPDATE_ORDER_STATUS']))
    		{
    			$mod_orders = new Databases_Tables_Orders();
    			
    			foreach ($event_array['UPDATE_ORDER_STATUS'] as $key => $val)
    			{
    				//initial update, need to update order code and payment status
    				if($val['order_code'])
    				{
    					$row = $mod_orders->fetchRow("orders_id = '".$key."'");
    					if(!empty($row))
    					{
    						$row->orders_code = $val['order_code'];
    						$row->orders_status = $val['status'];
    						$row->orders_payment_status = 1; //Paid
    						$row->save();
    						
    						$result += 1;
    					}
    				}else{ //update status only
    					$mod_orders->orders_id = $key;
    					$mod_orders->orders_status = $val['status'];
    					$mod_orders->UpdateStatus();
    					
    					$result += 1;
    				}
    			}
    		}
    	}
    	
    	return $result;
    }
}