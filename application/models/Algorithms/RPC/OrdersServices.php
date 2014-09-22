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
    			
    			if($pa['business_channel'])
    			{
    				$mod_log_sync->business_channel = $pa['business_channel'];
    			}
    			$mod_log_sync->AddLog();
    			
    			//combine array
    			
    			$log_val = Zend_Json::decode($pa['log_val']);
    			
    			if(!$event_array[$pa['log_event']])
    			{
    				$event_array[$pa['log_event']] = array();
    			}
    			
    			if(!$event_array[$pa['log_event']][$pa['log_key']])
    			{
    				$event_array[$pa['log_event']][$pa['log_key']] = array();
    			}
    			
    			$event_array[$pa['log_event']][$pa['log_key']][] = $log_val;
    		}
    		
    		//proceed
    		
    		/**
    		 * UPDATE_ORDER_STATUS
    		 */
    		if(!empty($event_array['UPDATE_ORDER_STATUS']))
    		{
    			$mod_orders = new Databases_Tables_Orders();
    			
    			foreach ($event_array['UPDATE_ORDER_STATUS'] as $key => $val_array)
    			{
    				foreach ($val_array as $val)
    				{
    					if($val['orders_code'] || $val['status'] || $val['payment_status'])
    					{
    						$row = $mod_orders->fetchRow("orders_id = '".$key."'");
    							
    						if($val['orders_code'])
    						{
    							$row->orders_code = $val['orders_code'];
    						}
    							
    						if($val['status'])
    						{
    							$row->orders_status = $val['status'];
    						}
    							
    						if($val['payment_status'])
    						{
    							$row->orders_payment_status = $val['payment_status'];
    						}
    							
    						$row->save();
    					}
    					
    					$result += 1;
    				}
    			}
    		}
    		
    		/**
    		 * UPDATE_PRODUCT_STATUS
    		 */
    		if(!empty($event_array['UPDATE_PRODUCT_STATUS']))
    		{
    			$mod_products = new Databases_Tables_MateriaProducts();
    			 
    			foreach ($event_array['UPDATE_PRODUCT_STATUS'] as $key => $val_array)
    			{
    				foreach ($val_array as $val)
    				{
    					$mod_products->product_id = $key;
    					$mod_products->status = $val['status'];
    					$mod_products->UpdateStatus();
    					
    					$result += 1;
    				}
    			}
    		}
    		
    		/**
    		 * UPDATE_SETS_STATUS
    		 */
    		if(!empty($event_array['UPDATE_SETS_STATUS']))
    		{
    			$mod_sets = new Databases_Tables_MateriaSets();
    		
    			foreach ($event_array['UPDATE_SETS_STATUS'] as $key => $val_array)
    			{
    				foreach ($val_array as $val)
    				{
	    				$mod_sets->sets_id = $key;
	    				$mod_sets->status = $val['status'];
	    				$mod_sets->UpdateStatus();
	    				
	    				$result += 1;
    				}
    			}
    		}
    		
    		/**
    		 * UPDATE_STOCK
    		 */
    		if(!empty($event_array['UPDATE_STOCK']))
    		{
    			$mod_products = new Databases_Tables_MateriaProducts();
    			
    			$items_plus = array();
    			$items_deduct = array();
    		
    			foreach ($event_array['UPDATE_STOCK'] as $key => $val_array)
    			{
    				foreach ($val_array as $val)
    				{
	    				if(1 == $val['action_type'])
	    				{
	    					if(!$items_plus[$key])
	    					{
	    						$items_plus[$key] = 0;
	    					}
	    					
	    					$items_plus[$key] += $val['qty'];
	    				}elseif(2 == $val['action_type'])
	    				{
	    					if(!$items_deduct[$key])
	    					{
	    						$items_deduct[$key] = 0;
	    					}
	    					
	    					$items_deduct[$key] += $val['qty'];
	    				}
	    				
	    				$result += 1;
    				}
    			}
    			
    			if(!empty($items_plus))
    			{
    				$mod_products->product_stock_array = $items_plus;
    				$mod_products->StockOperation(1);
    			}

    			if(!empty($items_deduct))
    			{
    				$mod_products->product_stock_array = $items_deduct;
    				$mod_products->StockOperation(2);
    			}
    		}
    		
    		/**
    		 * ADD_ITEM
    		 */
    		if(!empty($event_array['ADD_ITEM']))
    		{
    			$mod_log_sync_down = new Databases_Tables_LogSyncDown();
    			 
    			foreach ($event_array['ADD_ITEM'] as $key => $val)
    			{
    				$log = $mod_log_sync_down->fetchRow("log_id = '".$key."'");
    				if(!empty($log))
    				{
    					$log->log_status = 2; //Success
    					if($log->save())
    					{
    						$result += 1;
    					}
    				}
    			}
    		}
    		
    		/**
    		 * CHANGE_TABLE
    		 */
    		if(!empty($event_array['CHANGE_TABLE']))
    		{
    			$mod_log_sync_down = new Databases_Tables_LogSyncDown();
    			 
    			foreach ($event_array['CHANGE_TABLE'] as $key => $val)
    			{
    				$log = $mod_log_sync_down->fetchRow("log_id = '".$key."'");
    				if(!empty($log))
    				{
    					$log->log_status = 2; //Success
    					if($log->save())
    					{
    						$result += 1;
    					}
    				}
    			}
    		}
    	}
    	
    	return $result;
    }
    
    function LogSyncDown()
    {
    	$mod_log_sync_down = new Databases_Tables_LogSyncDown();
    	$data = $mod_log_sync_down->FetchLogToSync();
    	
    	return $data;
    }
}