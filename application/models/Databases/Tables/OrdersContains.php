<?php

class Databases_Tables_OrdersContains extends Zend_Db_Table
{
    protected $_name = 'orders-contains';
    var $items_array; //array
    var $orders_id;
    
    function InsertItems()
    {
    	$result = 0;
    	
    	if($this->orders_id && $this->items_array)
    	{
    		if($this->items_array['sets'])
    		{
    			$mod_orders_sets_contains = new Databases_Tables_OrdersSetsContains();

    			foreach($this->items_array['sets'] as $sets)
    			{
    				$row = $this->createRow();
    				$row->orders_id = $this->orders_id;
    				$row->orders_item_category = $sets['sets_category'];
    				$row->orders_item_id = $sets['sets_id'];
    				$row->orders_item_qty = 1; //no qty property for sets
    				$row->orders_item_price = $sets['sets_price'];
    				$row->orders_item_name = $sets['sets_name'];
    				$contains_id = $row->save();
    				
    				if($contains_id)
    				{
    					//insert set contains
    					$mod_orders_sets_contains->orders_contains_id = $contains_id;
    					$mod_orders_sets_contains->contains_array = $sets['contains'];
    					$mod_orders_sets_contains->InsertSetsContains();
    				}
    			}
    		}
    		
    		if($this->items_array['products'])
    		{
    			foreach($this->items_array['products'] as $product_id => $products)
    			{
    				$row = $this->createRow();
    				$row->orders_id = $this->orders_id;
    				$row->orders_item_category = $products[3];
    				$row->orders_item_id = $product_id;
    				$row->orders_item_qty = $products[0];
    				$row->orders_item_price = $products[1];
    				$row->orders_item_name = $products[2];
    				$contains_id = $row->save();
    			}
    		}
    		
    		$result = 1;
    	}
    	
    	return $result;
    }
}
