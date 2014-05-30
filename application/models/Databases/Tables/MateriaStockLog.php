<?php

class Databases_Tables_MateriaStockLog extends Zend_Db_Table
{
    protected $_name = 'materia-stock-log';
    var $products_id;
    var $action_category;
    var $action_type;
    var $qty;
    var $action_note_1;
    var $action_note_2;
    
    function MakePlusAction()
    {
    	$row_id = NULL;

    	if($this->action_category && $this->products_id && $this->action_type && $this->qty)
    	{
    		//create row
    		$row = $this->createRow();
    		$row->log_time = date("Y-m-d H:i:s");
    		$row->products_id = $this->products_id;
    		$row->action_category = $this->action_category;
    		$row->action_type = 1; //Plus
    		$row->qty = $this->qty;
    		if($this->action_note_1)
    		{
    			$row->action_note_1 = $this->action_note_1;
    		}
    		if($this->action_note_2)
    		{
    			$row->action_note_2 = $this->action_note_2;
    		}
    		
    		//balance due
    		$row_bd = $this->fetchRow("products_id='".$this->products_id."'", "log_id DESC");
    		if(!empty($row_bd))
    		{
    			$current_balance_due = $row_bd->balance_due;
    			$row->balance_due = $current_balance_due + $this->qty;
    		}else{ //current stock on hand
    			$mod_products = new Databases_Tables_MateriaProducts();
    			$get_stock = $mod_products->fetchRow("products_id='".$this->products_id."'");
    			$current_balance_due = $get_stock['stock_on_hand'];
    			$row->balance_due = $current_balance_due;
    		}
    		
    		$row_id = $row->save();
    	}
    	
    	return $row_id;
    }
    
	function MakeDeductAction()
    {
    	$row_id = NULL;

    	if($this->action_category && $this->products_id && $this->action_type && $this->qty)
    	{
    		//create row
    		$row = $this->createRow();
    		$row->log_time = date("Y-m-d H:i:s");
    		$row->products_id = $this->products_id;
    		$row->action_category = $this->action_category;
    		$row->action_type = 2; //Deduct
    		$row->qty = $this->qty;
    		if($this->action_note_1)
    		{
    			$row->action_note_1 = $this->action_note_1;
    		}
    		if($this->action_note_2)
    		{
    			$row->action_note_2 = $this->action_note_2;
    		}
    		
    		//balance due
    		$row_bd = $this->fetchRow("products_id='".$this->products_id."'", "log_id DESC");
    		if(!empty($row_bd))
    		{
    			$current_balance_due = $row_bd->balance_due;
    			$row->balance_due = $current_balance_due - $this->qty;
    		}else{ //current stock on hand
    			$mod_products = new Databases_Tables_MateriaProducts();
    			$get_stock = $mod_products->fetchRow("products_id='".$this->products_id."'");
    			$current_balance_due = $get_stock['stock_on_hand'];
    			$row->balance_due = $current_balance_due;
    		}
    		
    		$row_id = $row->save();
    	}
    	
    	return $row_id;
    }
}
