<?php

class Databases_Tables_MateriaPrices extends Zend_Db_Table
{
    protected $_name = 'materia-prices';
    var $product_id_array;
    var $business_channel_id;
    
    function FetchPricesById()
    {
    	$result = array();
    	
    	if(count($this->product_id_array) && $this->business_channel_id)
    	{
    		$data = $this->select();
    		$data->where("price_channel = ?", $this->business_channel_id);
    		$data->where("price_product IN (?)", $this->product_id_array);
    		$rows = $this->fetchAll($data);
    		
    		if(!empty($rows))
    		{
    			foreach($rows as $row)
    			{
    				$result[$row['price_product']] = $row['price_value'];
    			}
    		}
    	}
    	
    	return $result;
    }
    
    function DumpAll()
    {
    	$data = $this->fetchAll();
    	
    	$result = array();
    	
    	if(!empty($data))
    	{
    		foreach($data as $d)
    		{
    			if(!$result[$d['price_product']])
    			{
    				$result[$d['price_product']] = array();
    			}
    			
    			$result[$d['price_product']][$d['price_channel']] = $d['price_value'];
    		}
    	}
    	
    	return $result;
    }
}