<?php

/**
 * LogSync
 * @author peng.chen
 *
 * ===== Event Instruction =====
 * 
 * UPDATE_ORDER_STATUS
 * UPDATE_PRODUCT_INFO
 * UPDATE_SETS_INFO
 * UPDATE_PRODUCT_STATUS
 * ADD_NEW_PRODUCT
 * ADD_NEW_SETS
 * 
 * =============================
 */

class Databases_Tables_LogSync extends Zend_Db_Table
{
    protected $_name = 'log-sync';
    var $log_event;
    var $log_key;
    var $log_val;
    var $log_time;
    var $business_channel;
    
    function AddLog()
    {
    	$result = NULL;
    	
    	if($this->log_event && $this->log_key)
    	{
    		$row = $this->createRow();
    		$row->log_time = $this->log_time;
    		$row->log_event = $this->log_event;
    		$row->log_key = $this->log_key;
    		if($this->log_val)
    		{
    			$row->log_val = $this->log_val;
    		}
    		if($this->business_channel)
    		{
    			$row->business_channel = $this->business_channel;
    		}
    		$row->log_updated_time = date("Y-m-d H:i:s");
    		
    		$result = $row->save();
    	}
    	
    	return $result;
    }
}
