<?php

/**
 * LogSyncDown
 * @author peng.chen
 *
 * ===== Event Instruction =====
 * 
 * CHANGE_TABLE
 * ADD_ITEM
 * REMOVE_ITEM
 * 
 * =============================
 */

class Databases_Tables_LogSyncDown extends Zend_Db_Table
{
    protected $_name = 'log-sync-down';
    var $log_event;
    var $log_key;
    var $log_val;
    var $log_time;
    
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
    		$row->log_updated_time = date("Y-m-d H:i:s");
    		
    		$result = $row->save();
    	}
    	
    	return $result;
    }
    
    function UpdateLogStatus($id, $is_success) //is_success 1=Success 0=Failed
    {
    	if($id)
    	{
    		$row = $this->fetchRow("log_id='".$id."'");
    		$row->log_tried_times = $row->log_tried_times + 1;
    		$row->log_updated_time = date("Y-m-d H:i:s");
    		
    		if($is_success)
    		{
    			$row->log_status = 1;
    		}
    		
    		$result = $row->save();
    	}else{
    		$result = NULL;
    	}
    	
    	return $result;
    }
    
    function FetchLogToSync() //尝试次数不大于3
    {
    	$rows = $this->fetchAll("log_status=0 and log_tried_times<3");
    	
    	return $rows;
    }
}