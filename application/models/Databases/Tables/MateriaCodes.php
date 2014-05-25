<?php

class Databases_Tables_MateriaCodes extends Zend_Db_Table
{
    protected $_name = 'materia-codes';
    var $item_type;
    var $item_id;
    var $product_code;
    
    function InitialCodeGeneration()
    {
    	if($this->item_type)
    	{
    		$data = $this->select();
    		$data->from($this->_name, array("max(item_code) as m"));
    		$data->where("item_type = ?", $this->item_type);
    		$row = $this->fetchRow($data);
    	}
    	
    	if($row->m)
    	{
    		$result = $row->m + 1;
    	}else{
    		$result = "";
    	}
    	
    	return $result;
    }
    
    function IsExist($exclude=NULL) //1 means exculde self
    {
    	$result = FALSE;
    	
    	if($this->product_code && $this->item_type)
    	{
    		if($exclude)
    		{
    			$row = $this->fetchRow("item_type='".$this->item_type."' and item_code='".$this->product_code."' and item_id!='".$this->item_id."'");
    		}else{
    			$row = $this->fetchRow("item_type='".$this->item_type."' and item_code='".$this->product_code."'");
    		}
    		
    		if(!empty($row))
    		{
    			$result = TRUE;
    		}
    	}
    	
    	return $result;
    }
    
    function DumpAll()
    {
    	$result = array();
    	
    	if($this->item_type)
    	{
    		$data = $this->fetchAll("item_type=.".$this->item_type."'");
    	}else{
    		$data = array();
    	}
    	
    	if(!empty($data))
    	{
    		foreach($data as $row)
    		{
    			$result[$row['item_id']] = $row['item_code'];
    		}
    	}
    	
    	return $result;
    }
}
