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
    		$update_data = array("orders_status"=>2); //Sent
    		$this->db->update("orders", $update_data, "orders_id IN (".implode(",", $order_id).")");
    	}
    	
    	return $result;
    }
}