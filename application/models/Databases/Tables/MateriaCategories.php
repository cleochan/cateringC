<?php

class Databases_Tables_MateriaCategories extends Zend_Db_Table
{
    protected $_name = 'materia-categories';
    
    function CategoriesForOrders()
    {
    	$data = $this->fetchAll("category_status=1", "category_is_sets DESC, category_sequence");
    	
    	$result = array(
    		'sets' => array(),
    		'normal' => array()
    	);
    	
    	if(!empty($data))
    	{
    		foreach($data as $row)
    		{
    			if($this->IsInBusinessHour($row['category_valid_day'], $row['category_valid_time']))
    			{
    				if($row->category_is_sets)
    				{
    					$result['sets'][$row['categories_id']] = $row['category_name'];
    				}else{
    					$result['normal'][$row['categories_id']] = $row['category_name'];
    				}
    			}
    		}
    	}
    	
    	return $result;
    }
    
    function IsInBusinessHour($category_valid_day, $category_valid_time)
    {
    	$result = TRUE;
    	
    	if('ALL' != $category_valid_day || 'ALL' != $category_valid_time)
    	{
    		$current_day = date("w");
    		$current_hour = date("H");
    		
    		$day_range = explode("-", $category_valid_day);
    		if(2 == count($day_range))
    		{
    			if($current_day < $day_range[0] || $current_day > $day_range[1])
    			{
    				$result = FALSE;
    			}
    		}
    		
	    	if($result)
	    	{
	    		$time_range = explode("-", $category_valid_time);
	    		
	    		if(2 == count($time_range))
	    		{
	    			if($current_hour < $time_range[0] || $current_hour >= $time_range[1] )
	    			{
	    				$result = FALSE;
	    			}
	    		}
	    	}
    	}
    	
    	return $result;
    }
    
    function IsSets()
    {
    	$result = array();
    	
    	$data = $this->fetchAll("category_is_sets=1");
    	if(!empty($data))
    	{
    		foreach($data as $d)
    		{
    			$result[] = $d['categories_id'];
    		}
    	}
    	
    	return $result;
    }
    
	function DumpAll($category_is_sets=NULL)
    {
    	if($category_is_sets || 0 === $category_is_sets)
    	{
    		$where = "category_is_sets='".$category_is_sets."'";
    	}else{
    		$where = "categories_id > 0";
    	}
    	
    	$rows = $this->fetchAll($where, "category_sequence ASC");
    	
    	$result = array();
    	
    	if(!empty($rows))
    	{
    		foreach($rows as $row)
    		{
    			$result[$row['categories_id']] = $row['category_name'];
    		}
    	}
    	
    	return $result;
    }
}
