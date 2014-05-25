<?php
class Databases_Joins_SetsOperation
{	
	var $business_channel_id;
	var $current_sets_info;
	var $replacement_pool;
	var $original_contains_id;
	var $new_product_id;
	var $item_id;
	var $category_id;
	var $sets_id;
	
	function __construct(){
    	$this->db = Zend_Registry::get("db");
    }
    
    function FetchReplacements()
    {
    	$replacement_pool = array();
    	
    	if($this->current_sets_info && $this->business_channel_id)
    	{	
    		$data = $this->db->select();
    		$data->from("materia-sets-contains as c", array("contains_id", "contains_product_qty"));
    		$data->joinLeft("materia-sets-exchanges as e", "e.exchanges_contains_id=c.contains_id", array("exchanges_product_id"));
    		$data->joinLeft("materia-sets-prices as p", "p.sets_item_id=e.materia_sets_exchanges_id", array("sets_item_price"));
    		$data->joinLeft("materia-products as mp", "mp.products_id=e.exchanges_product_id", array("product_name", "product_category"));
    		$data->where("c.contains_product_exchangeable = ?", 1);
    		$data->where("c.contains_sets_id = ?", $this->current_sets_info['sets_id']);
    		$data->where("p.sets_channel_id = ?", $this->business_channel_id);
    		$data->where("p.sets_type = ?", 2); //replacement only
    		$data->where("mp.product_status = ?", 1);
    		$data->where("mp.stock_on_hand >= c.contains_product_qty");
    		$rows = $this->db->fetchAll($data);
    		
    		if(!empty($rows))
    		{
    			$contains_pool = array();
    			
    			foreach($rows as $row)
    			{
    				$contains_pool[] = $row['contains_id'];
    				
    				$replacement_pool[$row['contains_id']][$row['exchanges_product_id']] = array(
    					"category" => $row['product_category'],
    					"name" => $row['product_name'],
    					"qty" => $row['contains_product_qty'],
    					"price" => $row['sets_item_price']
    				);
    			}
    			
    			$contains_pool = array_unique($contains_pool);
    			
    			$data2 = $this->db->select();
    			$data2->from("materia-sets-contains as c", array("contains_id", "contains_product_id", "contains_category_id", "contains_product_qty"));
    			$data2->joinLeft("materia-sets-prices as p", "p.sets_item_id=c.contains_id", array("sets_item_price"));
    			$data2->joinLeft("materia-products as mp", "mp.products_id=c.contains_product_id", array("product_name"));
    			$data2->where("c.contains_id IN (?)", $contains_pool);
    			$data2->where("p.sets_type = ?", 1);//not replacement
    			$data2->where("p.sets_channel_id = ?", $this->business_channel_id);
    			$rows2 = $this->db->fetchAll($data2);
    			
    			if(!empty($rows2))
    			{
    				foreach($rows2 as $row2)
    				{
    					$replacement_pool[$row2['contains_id']][$row2['contains_product_id']] = array(
    						"category" => $row2['product_category'],
    						"name" => $row2['product_name'],
    						"qty" => $row2['contains_product_qty'],
    						"price" => $row2['sets_item_price']
    					);
    				}
    			}
    			
//     			echo "<pre>";
//     			print_r($replacement_pool);
//     			echo "<pre>";
//     			die;
    		}
    	}
    	
    	return $replacement_pool;
    }
    
    function UpdateSetsInfo()
    {
    	/**
    	 * $this->business_channel_id
    	 * $this->replacement_pool
    	 * $this->item_id
    	 * $this->original_contains_id
    	 * $this->new_product_id
    	 * $_session['eat-in']
    	 */
    	$model_plugin = new Algorithms_Extensions_Plugin();
    	
    	if(1 == $this->business_channel_id) //eat in
    	{
    		$price_diff = $this->replacement_pool[$this->original_contains_id][$this->new_product_id]['price'] * $this->replacement_pool[$this->original_contains_id][$this->new_product_id]['qty'] - $_SESSION['eat-in']['items']['sets'][$this->item_id]['contains'][$this->original_contains_id]['unit_price'];
    		
    		$_SESSION['eat-in']['items']['sets'][$this->item_id]['sets_price'] += $price_diff;
    		$_SESSION['eat-in']['items']['sets'][$this->item_id]['sets_price'] = $model_plugin->FormatPrice($_SESSION['eat-in']['items']['sets'][$this->item_id]['sets_price']);
    		
    		$_SESSION['eat-in']['items']['sets'][$this->item_id]['contains'][$this->original_contains_id]['product_id'] = $this->new_product_id;
    		$_SESSION['eat-in']['items']['sets'][$this->item_id]['contains'][$this->original_contains_id]['qty'] = $this->replacement_pool[$this->original_contains_id][$this->new_product_id]['qty'];
    		$_SESSION['eat-in']['items']['sets'][$this->item_id]['contains'][$this->original_contains_id]['unit_price'] = $model_plugin->FormatPrice($this->replacement_pool[$this->original_contains_id][$this->new_product_id]['price'] * $this->replacement_pool[$this->original_contains_id][$this->new_product_id]['qty']);
    		$_SESSION['eat-in']['items']['sets'][$this->item_id]['contains'][$this->original_contains_id]['product_name'] = $this->replacement_pool[$this->original_contains_id][$this->new_product_id]['name'];
    		$_SESSION['eat-in']['items']['sets'][$this->item_id]['contains'][$this->original_contains_id]['product_category'] = $this->replacement_pool[$this->original_contains_id][$this->new_product_id]['category'];
    		
    		//payment
    		$_SESSION['eat-in']['payment']['subtotal'] += $price_diff;
    		$_SESSION['eat-in']['payment']['total'] += $price_diff;
    		
    		if($_SESSION['eat-in']['payment']['cash'])
    		{
    			$_SESSION['eat-in']['payment']['change'] = $model_plugin->FormatPrice($_SESSION['eat-in']['payment']['cash'] - $_SESSION['eat-in']['payment']['total']);
    		}
    		
    		$_SESSION['eat-in']['payment']['subtotal'] = $model_plugin->FormatPrice($_SESSION['eat-in']['payment']['subtotal']);
    		$_SESSION['eat-in']['payment']['total'] = $model_plugin->FormatPrice($_SESSION['eat-in']['payment']['total']);
    		$_SESSION['eat-in']['payment']['change'] = $model_plugin->FormatPrice($_SESSION['eat-in']['payment']['change']);
    		
    	}
    	
    	return TRUE;
    }
    
    function DumpList()
    {
    	$rows_per_page = 30;
    	
    	//count total for pagination
    	$data = $this->db->select();
    	$data->from("materia-sets as s", array("count(sets_id) as ct"));
    	if($this->category_id)
    	{
    		$data->where("s.sets_category = ?", $this->category_id);
    	}
    	if($this->keyword)
    	{
    		$data->where("s.sets_name like ?", "%".$this->keyword."%");
    	}
    	if($this->page_id)
    	{
    		$data->limit($rows_per_page, $rows_per_page * ($this->page_id - 1));
    	}
    	$ct = $this->db->fetchRow($data);
    	
    	//dump all in condition
    	$data = $this->db->select();
    	$data->from("materia-sets as s", array("sets_id", "sets_name", "sets_status", "created_date", "updated_date"));
    	$data->joinLeft("materia-codes as c", "c.item_id=s.sets_id", array("item_code"));
    	$data->joinLeft("materia-categories as g", "g.categories_id=s.sets_category", array("category_name"));
    	$data->where("c.item_type = ?", 2); //sets
    	if($this->category_id)
    	{
    		$data->where("s.sets_category = ?", $this->category_id);
    	}
    	if($this->keyword)
    	{
    		$data->where("s.sets_name like ?", "%".$this->keyword."%");
    	}
    	if($this->page_id)
    	{
    		$data->limit($rows_per_page, $rows_per_page * ($this->page_id - 1));
    	}
    	$data->order(array("s.sets_status DESC", "s.updated_date DESC", "s.sets_id DESC"));
    	$rows = $this->db->fetchAll($data);
    	
    	//get eat-in prices
    	$data2 = $this->db->select();
    	$data2->from("materia-sets-contains as c", array("contains_id", "contains_sets_id", "contains_product_qty"));
    	$data2->joinLeft("materia-sets-prices as p", "p.sets_item_id=c.contains_id", array("sets_item_price"));
    	$data2->where("p.sets_type = ?", 1); //origine
    	$data2->where("p.sets_channel_id = ?", 1); //eat in
    	$rows2 = $this->db->fetchAll($data2);
    	
    	$prices = array();
    	$format_price = new Algorithms_Extensions_Plugin();
    	
    	if(!empty($rows2))
    	{
    		foreach($rows2 as $r)
    		{
    			if(!$prices[$r['contains_sets_id']])
    			{
    				$prices[$r['contains_sets_id']] = 0;
    			}
    			
    			$prices[$r['contains_sets_id']] += $r['sets_item_price'] * $r['contains_product_qty'];
    			$prices[$r['contains_sets_id']] = $format_price->FormatPrice($prices[$r['contains_sets_id']]);
    		}
    	}
    	
    	$result = array(
    		'total_pages' => ceil($ct['ct'] / $rows_per_page),
    		'current_page_info' => $rows,
    		'prices' => $prices
    	);
    	
    	return $result;
    }
    
    function DumpCompleteInfoOfSpecifiedSets()
    {
    	if($this->sets_id)
    	{
    		$data = $this->db->select();
    		$data->from("materia-sets as s", array("sets_id", "sets_name", "sets_image", "sets_desc", "sets_status", "created_date", "updated_date"));
    		$data->joinLeft("materia-categories as c", "c.categories_id=s.sets_category", array("category_name"));
    		$data->joinLeft("materia-codes as d", "d.item_id=s.sets_id", array("item_code"));
    		$data->where("s.sets_id = ?", $this->sets_id);
    		$data->where("d.item_type = ?", 2); //sets
    		$sets_general_info = $this->db->fetchRow($data);
    		
    		if(!empty($sets_general_info))
    		{
    			//contains
    			$data = $this->db->select();
    			$data->from("materia-sets-contains as s", array("contains_id", "contains_product_qty"));
    			$data->joinLeft("materia-categories as c", "c.categories_id=s.contains_category_id", array("category_name"));
    			$data->joinLeft("materia-products as p", "p.products_id=s.contains_product_id", array("product_name"));
    			$data->where("s.contains_sets_id = ?", $sets_general_info['sets_id']);
    			$sets_contains_info = $this->db->fetchAll($data);
    			
    			if(!empty($sets_contains_info))
    			{
    				$contains_id_array = array();
    				
    				foreach($sets_contains_info as $cinfo)
    				{
    					$contains_id_array[] = $cinfo['contains_id'];
    				}
    				
    				//contains prices
    				$mod_prices = new Databases_Tables_MateriaSetsPrices();
    				$mod_prices->item_id_array = $contains_id_array;
    				$contains_prices = $mod_prices->GetPrices(1);
    				
    				//exchanges
    				$data = $this->db->select();
    				$data->from("materia-sets-exchanges as e", array("materia_sets_exchanges_id", "exchanges_contains_id", "exchanges_product_id"));
    				$data->joinLeft("materia-products as p", "p.products_id=e.exchanges_product_id", array("product_name"));
    				$data->where("e.exchanges_contains_id IN (?)", $contains_id_array);
    				$sets_exchanges_info = $this->db->fetchAll($data);
    				
    				if(!empty($sets_exchanges_info))
    				{
    					$exchanges_id_array = array();
    					
    					foreach($sets_exchanges_info as $einfo)
    					{
    						$exchanges_id_array[] = $einfo['materia_sets_exchanges_id'];
    					}
    					
    					//exchanges prices
    					$mod_prices->item_id_array = $exchanges_id_array;
    					$exchanges_prices = $mod_prices->GetPrices(2);
    				}
    			}else{
    				$contains_prices = array();
    				$exchanges_prices = array();
    			}
    		}
    	}else{
    		echo "Key param missed.";
    		die;
    	}
    	
    	$result = array();
    	$result['sets_general_info'] = $sets_general_info;
    	$result['sets_contains_info'] = $sets_contains_info;
    	$result['contains_prices'] = $contains_prices;
    	$result['sets_exchanges_info'] = $sets_exchanges_info;
    	$result['exchanges_prices'] = $exchanges_prices;
    	
    	return $result;
    }
}



