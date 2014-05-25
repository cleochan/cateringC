<?php

class Databases_Tables_OrdersStatuses extends Zend_Db_Table
{
    protected $_name = 'orders-statuses';
    
    function DumpAll()
    {
    	$rows = $this->fetchAll();
    	 
    	$result = array();
    	 
    	if(!empty($rows))
    	{
    		foreach($rows as $row)
    		{
    			$result[$row['orders_statuses_id']] = $row['orders_statuses_internal_name'];
    		}
    	}
    	 
    	return $result;
    }
}
