<?php

class Databases_Tables_OrdersSetsContains extends Zend_Db_Table
{
    protected $_name = 'orders-sets-contains';
    var $contains_array; //array
    var $orders_contains_id;
    
    function InsertSetsContains()
    {
    	$result = 0;
    	
    	if($this->orders_contains_id && $this->contains_array)
    	{
    		foreach($this->contains_array as $contains_key => $contains_array)
    		{
    			$row = $this->createRow();
    			$row->orders_contains_id = $this->orders_contains_id;
    			$row->materia_contains_id = $contains_key;
    			$row->materia_category_id = $contains_array['product_category'];
    			$row->materia_product_id = $contains_array['product_id'];
    			$row->materia_product_name = $contains_array['product_name'];
    			$row->materia_product_qty = $contains_array['qty'];
    			$row->materia_product_price = $contains_array['unit_price'];
    			$result = $row->save();
    		}
    		
    		$result = 1;
    	}
    	
    	return $result;
    }
}
