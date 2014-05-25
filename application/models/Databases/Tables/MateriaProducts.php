<?php

class Databases_Tables_MateriaProducts extends Zend_Db_Table
{
    protected $_name = 'materia-products';
    var $category_id_array; // the id in the key, not value
    var $business_channel_id;
    var $product_id;
    var $product_stock_array;
    var $status;
    var $order_code;
    
    function FetchProductsByCategory()
    {
    	$result = array();
    	$product_id_array = array();
    	
    	if(count($this->category_id_array))
    	{
    		$c_array = array();
    		
    		foreach($this->category_id_array as $cia_key => $cia_value)
    		{
    			$c_array[] = $cia_key;
    		}
    		
    		$data = $this->select();
    		$data->where("product_status = ?", 1);
    		$data->where("stock_on_hand > ?", 0);
    		$data->where("product_category IN (?)", $c_array);
    		$rows = $this->fetchAll($data);
    		$rows = $rows->toArray();
    		
    		if(!empty($rows))
    		{
    			foreach($rows as $row)
    			{
    				$product_id_array[] = $row['products_id'];
    			}
    			
    			$get_prices = new Databases_Tables_MateriaPrices();
    			$get_prices->business_channel_id = $this->business_channel_id;
    			$get_prices->product_id_array = $product_id_array;
    			$prices_array = $get_prices->FetchPricesById();
    			
    			foreach($rows as $row)
    			{
    				if(!$result[$row['product_category']])
    				{
    					$result[$row['product_category']] = array();
    				}
    				$result[$row['product_category']][] = array(0=>$row['products_id'], 1=>$row['product_name'], 2=>$row['stock_on_hand'], 3=>$prices_array[$row['products_id']]);
    			}
    		}
    	}
    	
    	return $result;
    }
    
    function FetchProductById()
    {
    	$result = NULL;
    	
    	/**
    	 * $result = array(
    	 * 		'product_id' =>
    	 * 		'product_name' =>
    	 * 		'product_status' =>
    	 * 		'stock_on_hand' =>
    	 * 		'unit_price' =>
    	 * )
    	 */
    	if($this->product_id && $this->business_channel_id)
    	{
    		$product = $this->fetchRow("products_id = '".$this->product_id."'");
    		$product = $product->toArray();
    		
    		if(!empty($product))
    		{
    			$model_prices = new Databases_Tables_MateriaPrices();
    			$model_prices->business_channel_id = $this->business_channel_id;
    			$model_prices->product_id_array = array($product['products_id']);
    			$get_price = $model_prices->FetchPricesById(); //$get_price[$product['products_id']] is the price
    			
    			$result = array(
    				'product_id' => $product['products_id'],
    				'product_name' => $product['product_name'],
    				'product_status' => $product['product_status'],
    				'stock_on_hand' => $product['stock_on_hand'],
    				'unit_price' => $get_price[$product['products_id']],
    				'product_category' => $product['product_category']
    			);
    		}
    	}
    	
    	return $result;
    }
    
    function StockOperation($action_type) //1=Plus  2=Deduct
    {
    	$result = 0;
    	
    	if(!empty($this->product_stock_array) && $action_type)
    	{
    		$mod_stock_log = new Databases_Tables_MateriaStockLog();
    		$product_id_array = array();
    		
    		foreach($this->product_stock_array as $pkey => $pval)
    		{
    			$product_id_array[] = $pkey;
    		}
    		
    		$data = $this->select();
    		$data->where("products_id IN (?)", $product_id_array);
    		$rows = $this->fetchAll($data);
    		
    		if(!empty($rows))
    		{
    			foreach($rows as $row)
    			{
    				if(1 == $action_type)
    				{
    					$new_stock = $row['stock_on_hand'] + $this->product_stock_array[$row['products_id']];
    				}elseif(2 == $action_type)
    				{
    					$new_stock = $row['stock_on_hand'] - $this->product_stock_array[$row['products_id']];
    				}
    				
    				if(0 > $new_stock)
    				{
    					$new_stock = 0;
    				}
    				
    				//update table
					$a = $this->fetchRow("products_id=".$row['products_id']);
					$a->stock_on_hand = $new_stock;
					$a->save();
					
					//update log
					$mod_stock_log->products_id = $row['products_id'];
					
					if(1 == $action_type)
					{
						$mod_stock_log->action_category = 2; //客户退单
						$mod_stock_log->action_type = 1; //Plus
						$mod_stock_log->qty = $this->product_stock_array[$row['products_id']];
						$mod_stock_log->action_note_1 = $this->order_code;
						$mod_stock_log->MakePlusAction();
					}elseif(2 == $action_type)
					{
						$mod_stock_log->action_category = 1; //客户下单
						$mod_stock_log->action_type = 2; //Deduct
						$mod_stock_log->qty = $this->product_stock_array[$row['products_id']];
						$mod_stock_log->action_note_1 = $this->order_code;
						$mod_stock_log->MakeDeductAction();
					}
    			}
    		}
    		
    		$result = 1;
    	}
    	
    	return $result;
    }
    
    function FetchAllProducts($status=NULL)
    {
    	$result = array();
    	
    	if($status || 0 === $status)
    	{
    		$data = $this->fetchAll("product_status='".$status."'");
    	}else{
    		$data = $this->fetchAll();
    	}
    	
    	if(!empty($data))
    	{
    		foreach($data as $d)
    		{
    			$result[$d['products_id']] = $d['product_name'];
    		}
    	}
    	
    	return $result;
    }
    
    function FetchAllProductsWithCategory($status=NULL)
    {
    	$result = array();
    	
    	if($status || 0 === $status)
    	{
    		$data = $this->fetchAll("product_status='".$status."'");
    	}else{
    		$data = $this->fetchAll();
    	}
    	
    	if(!empty($data))
    	{
    		foreach($data as $d)
    		{
    			$result[$d['products_id']] = array($d['product_category'], $d['product_name']);
    		}
    	}
    	
    	return $result;
    }
    
    function FetchAllCategoriesWithProduct($status=NULL)
    {
    	$result = array();
    	
    	if($status || 0 === $status)
    	{
    		$data = $this->fetchAll("product_status='".$status."'");
    	}else{
    		$data = $this->fetchAll();
    	}
    	
    	if(!empty($data))
    	{
    		foreach($data as $d)
    		{
    			if(!$result[$d['product_category']])
    			{
    				$result[$d['product_category']] = array();
    			}
    			
    			$result[$d['product_category']][] = array($d['products_id'], $d['product_name']);
    		}
    	}
    	
    	return $result;
    }
    
    function UpdateStatus()
    {
    	$result = 0;
    	
    	if($this->product_id)
    	{
    		$row = $this->fetchRow("products_id='".$this->product_id."'");
    		
    		if($this->status)
    		{
    			$row->product_status = 1;
    		}else{
    			$row->product_status = 0;
    		}
    		
    		if($row->save())
    		{
    			$result = 1;
    		}
    	}
    	
    	return $result;
    }
}
