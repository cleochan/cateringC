<?php

class Databases_Tables_TablesNumber extends Zend_Db_Table
{
    protected $_name = 'tables-number';
    
    function DumpTables()
    {
    	$data = $this->fetchAll("table_id > 0", "table_id ASC");
    	
    	$result = array();
    	
    	if(!empty($data))
    	{
    		foreach ($data as $d)
    		{
    			$result[] = $d['table_id'];
    		}
    	}
    	
    	return $result;
    }
}