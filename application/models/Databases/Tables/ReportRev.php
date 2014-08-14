<?php

class Databases_Tables_ReportRev extends Zend_Db_Table
{
    protected $_name = 'report-rev';
    
    function AddData($data) //$data array
    {
    	if(!empty($data))
    	{
    		foreach($data as $d)
    		{
    			$row = $this->createRow();
    			$row->order_id = $d['order_id'];
    			$row->order_amount = $d['order_amount'];
    			$row->order_channel = $d['order_channel'];
    			$row->order_time = $d['order_time'];
    			$row->order_type = $d['order_type'];
    			$row->save();
    			
    			$last_order_id = $d['order_id'];
    		}
    		
    		if($last_order_id)
    		{
    			$mod_params = new Databases_Tables_Params();
    			$mod_params->UpdateVal("report_rev_last_id", $last_order_id);
    		}
    	}
    }
}
