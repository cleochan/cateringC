<?php
class Databases_Joins_ProductsOperation
{
	var $category_id;
	var $show_warning_stock;
	var $zone_id;
	var $keyword;
	var $page_id;
	var $product_id;
	
	function __construct(){
    	$this->db = Zend_Registry::get("db");
    }
    
    function DumpList()
    {
    	$rows_per_page = 30;
    	
    	//count total for pagination
    	$data = $this->db->select();
    	$data->from("materia-products as p", array("count(products_id) as ct"));
    	if($this->category_id)
    	{
    		$data->where("p.product_category = ?", $this->category_id);
    	}
    	if($this->zone_id)
    	{
    		$data->where("p.zone_id = ?", $this->zone_id);
    	}
    	if($this->show_warning_stock)
    	{
    		$data->where("p.stock_on_hand <= p.warning_stock_qty");
    	}
    	if($this->keyword)
    	{
    		$data->where("p.product_name like ?", "%".$this->keyword."%");
    	}
    	if($this->page_id)
    	{
    		$data->limit($rows_per_page, $rows_per_page * ($this->page_id - 1));
    	}
    	$ct = $this->db->fetchRow($data);
    	
    	//dump all in condition
    	$data = $this->db->select();
    	$data->from("materia-products as p", array("products_id", "product_name", "product_status", "stock_on_hand", "warning_stock_qty"));
    	$data->joinLeft("materia-categories as g", "g.categories_id=p.product_category", array("category_name"));
    	$data->joinLeft("zone-definition as z", "z.zone_id=p.zone_id", array("zone_code", "zone_name"));
    	$data->joinLeft("materia-codes as c", "c.item_id=p.products_id", array("item_code"));
    	$data->joinLeft("materia-prices as r", "r.price_product=p.products_id", array("price_value"));
    	$data->where("c.item_type = ?", 1); //product
    	$data->where("r.price_channel = ?", 1);
    	if($this->category_id)
    	{
    		$data->where("p.product_category = ?", $this->category_id);
    	}
    	if($this->zone_id)
    	{
    		$data->where("p.zone_id = ?", $this->zone_id);
    	}
    	if($this->show_warning_stock)
    	{
    		$data->where("p.stock_on_hand <= p.warning_stock_qty");
    	}
    	if($this->keyword)
    	{
    		$data->where("p.product_name like ?", "%".$this->keyword."%");
    	}
    	if($this->page_id)
    	{
    		$data->limit($rows_per_page, $rows_per_page * ($this->page_id - 1));
    	}
    	$data->order(array("p.product_status DESC", "g.category_sequence ASC", "p.products_id ASC"));
    	$rows = $this->db->fetchAll($data);
    	
    	$result = array(
    		'total_pages' => ceil($ct['ct'] / $rows_per_page),
    		'current_page_info' => $rows
    	);
    	
    	return $result;
    }
    
    function FetchSpecifiedProduct()
    {
    	$product_info = array();
    	$prices_info = array();
    	
    	if($this->product_id)
    	{
    		$data = $this->db->select();
    		$data->from("materia-products as p", array("product_category", "product_name", "product_image", "product_desc", "product_status", "warning_stock_qty", "zone_id"));
    		$data->joinLeft("materia-codes as c", "c.item_id=p.products_id", array("item_code"));
    		$data->where("c.item_type = ?", 1); //product
    		$data->where("p.products_id = ?", $this->product_id);
    		$row = $this->db->fetchRow($data);
    		
    		if(!empty($row))
    		{
    			$product_info = $row;
    			
    			//prices
    			$data2 = $this->db->select();
    			$data2->from("materia-prices", array("price_channel", "price_value"));
    			$data2->where("price_product = ?", $this->product_id);
    			$prices_info = $this->db->fetchAll($data2);
    			
    			if(!empty($prices_info))
    			{
    				foreach($prices_info as $pi)
    				{
    					$prices_info[$pi['price_channel']] = $pi['price_value'];
    				}
    			}
    		}
    	}
    	
    	return array($product_info, $prices_info);
    }
}




