<?php
class Databases_Joins_GetSetsInfo
{
    var $sets_id_array;
    var $business_channel_id;
	
    function __construct(){
    	$this->db = Zend_Registry::get("db");
    }
    
	function GetSetsPricesAndStock()
	{
		$result_price = array();
		$result_stock = array();
		$result_contains = array();
		
		$model_plugin = new Algorithms_Extensions_Plugin();
		
		if(count($this->sets_id_array) && $this->business_channel_id)
		{
			$data = $this->db->select();
			$data->from("materia-sets-contains as c", array("contains_id", "contains_sets_id", "contains_product_id", "contains_product_qty"));
			$data->joinLeft("materia-sets-prices as p", "p.sets_item_id=c.contains_id", array("sets_item_price"));
			$data->joinLeft("materia-products as m", "m.products_id=c.contains_product_id", array("product_name", "stock_on_hand", "product_category"));
			$data->joinLeft("zone-definition as z", "z.zone_id=m.zone_id", array("zone_code"));
			$data->where("p.sets_type = ?", 1);
			$data->where("p.sets_channel_id = ?", $this->business_channel_id);
			$data->where("c.contains_sets_id IN (?)", $this->sets_id_array);
			$rows = $this->db->fetchAll($data);
			echo "llkkll";die;
			if(!empty($rows))
			{
				$n=1;
				foreach($rows as $row)
				{
					//get package price
					if(0 !== $result_price[$row['contains_sets_id']] && !$result_price[$row['contains_sets_id']])
					{
						$result_price[$row['contains_sets_id']] = 0;
					}
					
					$result_price[$row['contains_sets_id']] += $row['contains_product_qty'] * $row['sets_item_price'];
					
					//get min stock
					if(0 !== $result_stock[$row['contains_sets_id']] && !$result_stock[$row['contains_sets_id']])
					{
						$result_stock[$row['contains_sets_id']] = intval($row['stock_on_hand']);
					}
					
					if($result_stock[$row['contains_sets_id']] > intval($row['stock_on_hand']))
					{
						$result_stock[$row['contains_sets_id']] = intval($row['stock_on_hand']);
					}
					
					//get contains array
					if(!$result_contains[$row['contains_sets_id']])
					{
						$result_contains[$row['contains_sets_id']] = array();
					}
					echo "ddd";die;
					$result_contains[$row['contains_sets_id']][$row['contains_id']] = array(
						"product_id" => $row['contains_product_id'],
						"qty" => $row['contains_product_qty'],
						"unit_price" => $model_plugin->FormatPrice($row['sets_item_price'] * $row['contains_product_qty']),
						"product_name" => $row['product_name'],
						"product_category" => $row['product_category'],
						"zone_code" => $row['zone_code']
					);
				}
			}
		}
		
		$result = array(
			"price" => $result_price,
			"stock" => $result_stock,
			"contains" => $result_contains
		);
// 		echo "<pre>";
// 		print_r($result);
// 		echo "<pre>";
// 		die;
		return $result;
	}
}