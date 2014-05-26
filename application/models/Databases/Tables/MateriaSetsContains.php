<?php

class Databases_Tables_MateriaSetsContains extends Zend_Db_Table
{
    protected $_name = 'materia-sets-contains';
    var $sets_id;
    
    function InitialContains()
    {
    	$result = array();
    	
    	if($this->sets_id)
    	{
    		$data = $this->fetchAll("contains_sets_id='".$this->sets_id."'");
    		
    		if(!empty($data))
    		{
    			foreach($data as $d)
    			{
    				$result[$d['contains_id']] = $d['contains_product_id'];
    			}
    		}
    	}
    	
    	return $result;
    }
}
