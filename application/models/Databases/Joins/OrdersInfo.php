<?php
class Databases_Joins_OrdersInfo
{	
    var $orders_status; // * search
    var $payment_status; // * search
    var $orders_channel; // * search
    var $order_id;
    var $order_code; // * search
    var $row_limits;
    var $select_order;
    
    var $users_id; // * search
    var $device_id; // * search
    var $orders_type; // * search
    var $page_id; // * search
    var $time_start; // * search
    var $time_end; // * search
    var $skip_pagination; //for export
    
	function __construct(){
    	$this->db = Zend_Registry::get("db");
    }
    
    function FetchBriefOrdersInfo($limit=NULL) //1=limit in current user
    {
    	$data = $this->db->select();
    	$data->from("orders as o", array("orders_id", "orders_code", "orders_time", "orders_amount", "orders_channel", "orders_type", "table_id"));
    	$data->joinLeft("business-channels as bc", "bc.channels_id=o.orders_channel", array("channel_name"));
    	$data->joinLeft("orders-contains as c", "c.orders_id=o.orders_id", array("orders_item_id"));
    	$data->joinLeft("materia-categories as g", "g.categories_id=c.orders_item_category", array("category_is_sets"));
    	$data->joinLeft("orders-addresses as a", "a.orders_id=o.orders_id", array("address_details", "phone", "delivery_notes"));
    	$data->joinLeft("addresses as addr", "addr.addresses_id=a.addresses_id", array("address_street", "address_number", "address_building"));
    	
    	if($this->orders_channel)
    	{
    		$data->where("o.orders_channel = ?", $this->orders_channel);
    	}
    	if($this->orders_status)
    	{
    		$data->where("o.orders_status = ?", $this->orders_status);
    	}
    	if($this->payment_status || 0 === $this->payment_status)
    	{
    		$data->where("o.orders_payment_status = ?", $this->payment_status);
    	}
    	if(1 == $limit)
    	{
    		$data->where("o.users_id = ?", $_SESSION['user_info']['users_id']);
    	}
    	if($this->select_order)
    	{
    		$data->order("orders_id ASC");
    	}else{
    		$data->order("orders_id DESC");
    	}
    	
    	$rows = $this->db->fetchAll($data);
    	
    	$result = array();
    	
    	$mod_products = new Databases_Tables_MateriaProducts();
    	$all_products = $mod_products->FetchAllProducts();
    	
    	$mod_sets = new Databases_Tables_MateriaSets();
    	$all_sets = $mod_sets->FetchAllSets();
    	
    	if(!empty($rows))
    	{
    		foreach($rows as $row)
    		{
    			if($row['category_is_sets'])
    			{
    				$temp_order_name[$row['orders_code']][] = $all_sets[$row['orders_item_id']];
    			}else{
    				$temp_order_name[$row['orders_code']][] = $all_products[$row['orders_item_id']];
    			}
    		}
    		
    		$orders_poll = array();
    		
    		foreach ($rows as $row2)
    		{
    			$result[$row2['orders_id']] = array(
    				'orders_id' => $row2['orders_id'],
    				'orders_time' => $row2['orders_time'],
    				'orders_code' => $row2['orders_code'],
    				'orders_type' => $row2['orders_type'],
    				'orders_amount' => $row2['orders_amount'],
    				'orders_items' => implode(", ", $temp_order_name[$row2['orders_code']]),
    				'channel_id' => $row2['orders_channel'],
    				'channel_name' => $row2['channel_name'],
    				'address_details' => $row2['address_details'],
    				'phone' => $row2['phone'],
    				'delivery_notes' => $row2['delivery_notes'],
    				'address_street' => $row2['address_street'],
    				'address_number' => $row2['address_number'],
    				'address_building' => $row2['address_building']
    			);
    			
    			if(!in_array($row2['orders_id'], $orders_poll))
    			{
    				$orders_poll[] = $row2['orders_id'];
    			}
    			
    			if($this->row_limits)
    			{
    				if(count($orders_poll) >= $this->row_limits)
    				{
    					break;
    				}
    			}
    		}
    	}
    	
    	return $result;
    }
    
    function GetSpecifiedOrderDetails($type=NULL) //type 1=by code null=by id
    {
//     	$zone = $this->ZoneIdArray();
    	
    	$result = array(
    			'items' => array(
    					'sets' => array(),
    					'products' => array()
    			),
    			'payment' => array(
    					'order_db_id' => NULL,
    					'order_id' => NULL,
    					'order_status' => NULL,
    					'ctime' => NULL,
    					'subtotal' => 0,
    					'total' => 0,
    					'cash' => 0,
    					'change' => 0,
    					'user_alias' => NULL,
    					'table_id' => NULL
    			)
    		);
    	
    	//fetch order details
    	if($this->order_id || $this->order_code)
    	{
    		$data = $this->db->select();
    		$data->from("orders as o", array("orders_id", "orders_code", "orders_status", "orders_time", "orders_type", "orders_amount", "orders_cash", "orders_change", "orders_subtotal", "orders_coupon", "orders_discount", "users_id", "table_id"));
    		$data->joinLeft("orders-contains as c", "c.orders_id=o.orders_id", array("orders_contains_id", "orders_item_qty", "orders_item_price", "orders_item_name", "orders_item_id"));
    		$data->joinLeft("orders-sets-contains as s", "s.orders_contains_id=c.orders_contains_id", array("materia_product_name", "materia_product_qty", "materia_product_id"));
    		if(1 == $type)
    		{
    			$data->where("o.orders_code = ?", $this->order_code);
    		}else{
    			$data->where("o.orders_id = ?", $this->order_id);
    		}
    		
    		$rows = $this->db->fetchAll($data);
    		
    		if(!empty($rows))
    		{
    			$n = 0;
    			
    			foreach($rows as $row)
    			{
    				if(!$n)
    				{
    					$result['payment']['order_db_id'] = $row['orders_id'];
    					$result['payment']['order_id'] = $row['orders_code'];
    					$result['payment']['order_type'] = $row['orders_type'];
    					$result['payment']['order_status'] = $row['orders_status'];
    					$result['payment']['ctime'] = $row['orders_time'];
    					$result['payment']['subtotal'] = $row['orders_subtotal'];
    					$result['payment']['total'] = $row['orders_amount'];
    					$result['payment']['cash'] = $row['orders_cash'];
    					$result['payment']['change'] = $row['orders_change'];
    					$result['payment']['user_alias'] = NULL;
    					$result['payment']['table_id'] = $row['table_id'];
    					$n += 1;
    				}
    				
    				if($row['materia_product_qty']) //sets
    				{
    					if($result['items']['sets'][$row['orders_contains_id']]) //update
    					{
    						$result['items']['sets'][$row['orders_contains_id']]['contains'][] = array($row['materia_product_name'], $row['materia_product_qty']);
    					}else{ //create
    						$result['items']['sets'][$row['orders_contains_id']] = array(
    								'sets_id' => $row['orders_item_id'],
    								'sets_name' => $row['orders_item_name'],
    								'sets_price' => $row['orders_item_price'],
    								'contains' => array(array($row['materia_product_name'], $row['materia_product_qty']))
    						);
    					}
    				}else{ //products
    					$result['items']['products'][$row['orders_item_id']] = array($row['orders_item_qty'], $row['orders_item_price'], $row['orders_item_name']);
    				}
    			}
    		}
    	}
		
    	return $result;
    }
    
//     function ZoneIdArray()
//     {
//     	$data = $this->db->select();
//     	$data->from("materia-products as p", array("products_id"));
//     	$data->joinLeft("zone-definition as z", "z.zone_id=p.zone_id", array("zone_id", "zone_code", "zone_name"));
//     	$rows = $this->db->fetchAll($data);
    	
//     	$result = array();
    	
//     	if(!empty($rows))
//     	{
//     		foreach($rows as $row)
//     		{
//     			$result[$row['products_id']] = array(
//     				'zone_id' => $row['zone_id'],
//     				'zone_code' => $row['zone_code'],
//     				'zone_name' => $row['zone_name']
//     			);
//     		}
//     	}
    	
//     	return $result;
//     }
    
    function DumpLog()
    {
    	$rows_per_page = 30;
    	
    	//count total for pagination
    	$data = $this->db->select();
    	$data->from("orders as o", array("count(orders_id) as ct"));
    	 
    	if($this->orders_status)
    	{
    		$data->where("o.orders_status = ?", $this->orders_status);
    	}
    	if($this->payment_status)
    	{
    		$data->where("o.orders_payment_status = ?", $this->payment_status);
    	}
    	if($this->orders_channel)
    	{
    		$data->where("o.orders_channel = ?", $this->orders_channel);
    	}
    	if($this->order_code)
    	{
    		$data->where("o.orders_code = ?", $this->order_code);
    	}
    	if($this->users_id)
    	{
    		$data->where("o.users_id = ?", $this->users_id);
    	}
    	if($this->device_id)
    	{
    		$data->where("o.device_id = ?", $this->device_id);
    	}
    	if($this->orders_type)
    	{
    		$data->where("o.orders_type = ?", $this->orders_type);
    	}
    	if($this->time_start)
    	{
    		$data->where('o.orders_time >= ?', $this->time_start." 00:00:00");
    	}
    	
    	if($this->time_end)
    	{
    		$data->where('o.orders_time <= ?', $this->time_end." 23:59:59");
    	}
    	if($this->page_id)
    	{
    		$data->limit($rows_per_page, $rows_per_page * ($this->page_id - 1));
    	}
    	$ct = $this->db->fetchRow($data);
    	
    	//dump all in condition
    	$data = $this->db->select();
    	$data->from("orders as o", array("orders_code", "orders_payment_status", "orders_type", "orders_time", "orders_amount", "table_id"));
    	$data->joinLeft("business-channels as b", "b.channels_id=o.orders_channel", array("channel_name"));
    	$data->joinLeft("orders-statuses as s", "s.orders_statuses_id=o.orders_status", array("orders_statuses_internal_name as status_name"));
    	$data->joinLeft("users as u", "u.users_id=o.users_id", array("user_alias"));
    	$data->joinLeft("clients-definition as c", "c.client_id=o.device_id", array("client_name"));
    
    	if($this->orders_status)
    	{
    		$data->where("o.orders_status = ?", $this->orders_status);
    	}
    	if($this->payment_status)
    	{
    		$data->where("o.orders_payment_status = ?", $this->payment_status);
    	}
    	if($this->orders_channel)
    	{
    		$data->where("o.orders_channel = ?", $this->orders_channel);
    	}
    	if($this->order_code)
    	{
    		$data->where("o.orders_code = ?", $this->order_code);
    	}
    	if($this->users_id)
    	{
    		$data->where("o.users_id = ?", $this->users_id);
    	}
    	if($this->device_id)
    	{
    		$data->where("o.device_id = ?", $this->device_id);
    	}
    	if($this->orders_type)
    	{
    		$data->where("o.orders_type = ?", $this->orders_type);
    	}
    	if($this->time_start)
    	{
    		$data->where('o.orders_time >= ?', $this->time_start." 00:00:00");
    	}
    
    	if($this->time_end)
    	{
    		$data->where('o.orders_time <= ?', $this->time_end." 23:59:59");
    	}
    	if($this->page_id && !$this->skip_pagination)
    	{
    		$data->limit($rows_per_page, $rows_per_page * ($this->page_id - 1));
    	}
    	
    	$data->order("o.orders_id DESC");
    	$rows = $this->db->fetchAll($data);
    	
    	$result = array(
    		'total_pages' => ceil($ct['ct'] / $rows_per_page),
    		'current_page_info' => $rows
    	);
    	
    	return $result;
    }
    
    function DumpSoldItemLog()
    {
    	$rows_per_page = 30;
    	
    	//count total for pagination
    	$data = $this->db->select();
    	$data->from("orders as o", array("count(orders_id) as ct"));
    	 
    	if($this->time_start)
    	{
    		$data->where('o.orders_time >= ?', $this->time_start." 00:00:00");
    	}
    	
    	if($this->time_end)
    	{
    		$data->where('o.orders_time <= ?', $this->time_end." 23:59:59");
    	}
    	
    	if($this->page_id)
    	{
    		$data->limit($rows_per_page, $rows_per_page * ($this->page_id - 1));
    	}
    	$ct = $this->db->fetchRow($data);
    	
    	//dump all in condition
    	$data = $this->db->select();
    	$data->from("orders as o", array("orders_code", "orders_time"));
    	$data->joinLeft("orders-statuses as s", "s.orders_statuses_id=o.orders_status", array("orders_statuses_internal_name as status_name"));
    	$data->joinLeft("orders-contains as c", "c.orders_id=o.orders_id", array("orders_item_category", "orders_item_id", "orders_item_qty", "orders_item_price", "orders_item_name"));
    	$data->joinLeft("orders-sets-contains as n", "n.orders_contains_id=c.orders_contains_id", array("materia_category_id", "materia_product_id", "materia_product_name", "materia_product_qty", "materia_product_price"));
    	
    	if($this->time_start)
    	{
    		$data->where('o.orders_time >= ?', $this->time_start." 00:00:00");
    	}
    
    	if($this->time_end)
    	{
    		$data->where('o.orders_time <= ?', $this->time_end." 23:59:59");
    	}
    	if($this->page_id && !$this->skip_pagination)
    	{
    		$data->limit($rows_per_page, $rows_per_page * ($this->page_id - 1));
    	}
    	
    	$data->order("o.orders_id DESC");
    	$rows = $this->db->fetchAll($data);
    	
    	$result = array(
    		'total_pages' => ceil($ct['ct'] / $rows_per_page),
    		'current_page_info' => $rows
    	);
    	
    	return $result;
    }
    
    function DumpLogOnWechat()
    {
    	$data = $this->db->select();
    	$data->from("orders as o", array("orders_id", "orders_code", "orders_time", "table_id", "orders_amount", "orders_type", "orders_payment_status", "users_id"));
    	$data->joinLeft("orders-statuses as s", "s.orders_statuses_id=o.orders_status", array("orders_statuses_external_name as status_name"));
    	
    	if($this->users_id)
    	{
    		$data->where("users_id = ?", $this->users_id);
    	}
    	
    	$data->where("orders_time >= ?", date("Y-m-d")." 00:00:00");
    	$data->order("orders_id DESC");
    	$rows = $this->db->fetchAll($data);
    	
    	return $rows;
    }
}




