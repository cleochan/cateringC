<?php

/**
 * LogSyncDown
 * @author peng.chen
 *
 * ===== Event Instruction =====
 * 
 * CHANGE_TABLE
 * ADD_ITEM
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
    var $log_desc;
    
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
    		if($this->log_desc)
    		{
    			$row->log_desc = $this->log_desc;
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
    			$row->log_status = 1; //Successful
    		}elseif(!$is_success && 3 < $row->log_tried_times){  //尝试次数不大于3
    			$row->log_status = 2; //Failed
    		}
    		
    		$result = $row->save();
    	}else{
    		$result = NULL;
    	}
    	
    	return $result;
    }
    
    function FetchLogToSync()
    {
    	$rows = $this->fetchAll("log_status=0 and log_time >= '".date(Y-m-d)." 00:00:00'");
    	
    	return $rows;
    }
    
    function DumpList()
    {
    	$rows = $this->select();
    	$rows->where("log_time >= ?", date("Y-m-d")." 00:00:00");
    	$rows->order("log_id DESC");
    	$data = $this->fetchAll($rows);
    	
    	return $data;
    }
}





