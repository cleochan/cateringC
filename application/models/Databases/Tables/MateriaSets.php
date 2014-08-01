<?php

class Databases_Tables_MateriaSets extends Zend_Db_Table
{
    protected $_name = 'materia-sets';
    var $category_id_array; // the id in the key, not value
    var $business_channel_id;
    var $sets_id;
    var $status;
    
    function FetchProductsByCategory()
    {
    	$result = array();
    	$product_id_array = array();
    	
    	if(count($this->category_id_array))
    	{
    		$c_array = array();
    		
    		foreach($this->category_id_array as $cia_key => $cia_value)
    		{
    			$c_array[] = $cia_key;
    		}
    		
    		$data = $this->select();
    		$data->where("sets_status = ?", 1);
    		$data->where("sets_category IN (?)", $c_array);
    		$rows = $this->fetchAll($data);
    		$rows = $rows->toArray();
    		
    		if(!empty($rows))
    		{
    			foreach($rows as $row)
    			{
    				$product_id_array[] = $row['sets_id'];
    			}
    			
    			$get_prices_and_stock = new Databases_Joins_GetSetsInfo();
    			$get_prices_and_stock->business_channel_id = $this->business_channel_id;
    			$get_prices_and_stock->sets_id_array = $product_id_array;
    			$get_result = $get_prices_and_stock->GetSetsPricesAndStock();
    			echo "alal2";die;
    			//get sets codes
    			$mod_codes = new Databases_Tables_MateriaCodes();
    			$mod_codes->item_type = 2; //sets
    			$codes_array = $mod_codes->DumpAll();
    			
    			foreach($rows as $row)
    			{
    				if(!$result[$row['sets_category']])
    				{
    					$result[$row['sets_category']] = array();
    				}
    				if($get_result['stock'][$row['sets_id']]) //remvoe out of stock
    				{
    					$result[$row['sets_category']][] = array(0=>$row['sets_id'], 1=>$row['sets_name'], 2=>$get_result['stock'][$row['sets_id']], 3=>$get_result['price'][$row['sets_id']], 4=>$codes_array[$row['sets_id']]);
    				}
    			}
    		}
    	}
    	
    	return $result;
    }
    
    function FetchSetsInfo()
    {
    	$result = array();
    	
    	if($this->sets_id)
    	{
    		$data = $this->fetchRow("sets_id='".$this->sets_id."'");
    		$result = $data->toArray();
    	}
    	
    	return $result;
    }
    
    function FetchAllSets()
    {
    	$result = array();
    	
    	$data = $this->fetchAll();
    	if(!empty($data))
    	{
    		foreach($data as $d)
    		{
    			$result[$d['sets_id']] = $d['sets_name'];
    		}
    	}
    	
    	return $result;
    }
    
    function UpdateStatus()
    {
    	$result = 0;
    	
    	if($this->sets_id)
    	{
    		$row = $this->fetchRow("sets_id='".$this->sets_id."'");
    		
    		if($this->status)
    		{
    			$row->sets_status = 1;
    		}else{
    			$row->sets_status = 0;
    		}
    		
    		if($row->save())
    		{
    			$result = 1;
    		}
    	}
    	
    	return $result;
    }

    function FetchAllSetsWithCategory($status=NULL)
    {
    	$result = array();
    	 
    	if($status || 0 === $status)
    	{
    		$data = $this->fetchAll("sets_status='".$status."'");
    	}else{
    		$data = $this->fetchAll();
    	}
    	 
    	if(!empty($data))
    	{
    		foreach($data as $d)
    		{
    			$result[$d['sets_id']] = array($d['sets_category'], $d['sets_name']);
    		}
    	}
    	 
    	return $result;
    }
}
