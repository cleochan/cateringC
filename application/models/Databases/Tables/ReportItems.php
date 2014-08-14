<?php

class Databases_Tables_ReportItems extends Zend_Db_Table
{
    protected $_name = 'report-items';
    
    function AddData($data) //$data array
    {
    	if(!empty($data))
    	{
    		foreach($data as $d)
    		{
    			$row = $this->createRow();
    			$row->rdate = $d['rdate'];
    			$row->rcategory = $d['rcategory'];
    			$row->ritem_id = $d['ritem_id'];
    			$row->ritem_qty = $d['ritem_qty'];
    			$row->ritem_price = $d['ritem_price'];
    			$row->ritem_name = $d['ritem_name'];
    			$row->save();
    			
    			$cid = $d['cid'];
    		}
    		
    		if($cid)
    		{
    			$mod_params = new Databases_Tables_Params();
    			$mod_params->UpdateVal("report_items_last_id", $cid);
    		}
    	}
    }
}
