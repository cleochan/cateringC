<?php

class Databases_Tables_MateriaSetsPrices extends Zend_Db_Table
{
    protected $_name = 'materia-sets-prices';
    var $item_id_array;
    
    function GetPrices($sets_type, $channel_id=NULL) //if it's null, get all channels; sets type 1 origine, type 2 exchanges
    {
    	$result = array();
    	
    	if(!empty($this->item_id_array))
    	{
    		$data = $this->select();
    		$data->where("sets_item_id IN (?)", $this->item_id_array);
    		$data->where("sets_type = ?", $sets_type);
    		if($channel_id)
    		{
    			$data->where("sets_channel_id = ?", $channel_id);
    		}
    		$rows = $this->fetchAll($data);
    		
    		if(!empty($rows))
    		{
    			foreach($rows as $row)
    			{
    				if(!$result[$row['sets_item_id']])
    				{
    					$result[$row['sets_item_id']] = array();
    				}
    				
    				$result[$row['sets_item_id']][$row['sets_channel_id']] = $row['sets_item_price'];
    			}
    		}
    	}
    	
    	return $result;
    }
}