<?php

class Algorithms_Core_OrdersInfoGeneration
{
	var $item_id;
	var $order_id;
	
	function __construct(){
		$this->db = Zend_Registry::get("db");
	}

	/**
	 * $_SESSION['eat-in'] = array(
	 * 		'items' => array(
	 * 					'sets' => array(
	 * 								0 => array(
	 * 										'sets_id' => [sets_id]
	 * 										'sets_category' => [sets_category]
	 * 										'sets_name' => [sets_name]
	 * 										'sets_price' = > [sets_price]
	 * 										'contains' => array(
	 * 												[contains_id] => array([product_id], [qty], [unit_price], [product_name], [category_id])
	 * 												[contains_id] => array([product_id], [qty], [unit_price], [product_name], [category_id])
	 * 												[contains_id] => array([product_id], [qty], [unit_price], [product_name], [category_id])
	 * 										)
	 * 								)
	 * 					)
	 * 					'products' => array(
	 * 								[product_id] => array([qty], [unit_price], [product_name], [category_id])
	 * 								[product_id] => array([qty], [unit_price], [product_name], [category_id])
	 * 					)
	 * 		)
	 * 		'payment' => array(
	 * 					'order_id' => '[order_id]'
	 * 					'ctime' => '[ctime]'
	 * 					'subtotal' => [subtotal]
	 * 					'used_coupon' => [used_coupon]
	 * 					'discount' => [discount]
	 * 					'total' => [total]
	 * 					'cash' => [cash]
	 * 					'change' => [change]
	 * 					'user_code' => [user_code]
	 * 					'user_alias' => [user_alias]
	 * 					'member_id' => [member_id]
	 * 					'table_id' => [table_id]
	 * 		)
	 * )
	 */
	function InitialEatInSession($to_array=NULL) //to_array = make array not session
	{
		$result = FALSE;
		
		if($to_array)
		{
			$result = array(
					'items' => array(
							'sets' => array(),
							'products' => array()
					),
					'payment' => array(
							'order_id' => NULL,
							'ctime' => date("Y-m-d H:i:s"),
							'subtotal' => 0,
							'used_coupon' => 0,
							'discount' => 0,
							'total' => 0,
							'cash' => 0,
							'change' => 0,
							'user_code' => $_SESSION['user_info']['user_code'],
							'user_alias' => $_SESSION['user_info']['user_alias'],
							'users_id' => NULL,
							'member_id' => NULL,
							'table_id' => NULL
					)
			);
		}elseif(!$_SESSION['eat-in']){
				$_SESSION['eat-in'] = array(
						'items' => array(
								'sets' => array(),
								'products' => array()
						),
						'payment' => array(
								'order_id' => NULL,
								'ctime' => date("Y-m-d H:i:s"),
								'subtotal' => 0,
								'used_coupon' => 0,
								'discount' => 0,
								'total' => 0,
								'cash' => 0,
								'change' => 0,
								'user_code' => $_SESSION['user_info']['user_code'],
								'user_alias' => $_SESSION['user_info']['user_alias'],
								'member_id' => NULL,
								'table_id' => NULL
						)
				);
			
				$result = TRUE;
		}
		
		return $result;
	}
	
	function CleanEatInSession()
	{
		unset($_SESSION['eat-in']);
		
		return TRUE;
	}
	
	function AddProductIntoEatInSession()
	{
		$result = array(
			"qty_in_cart" => 0,
			"amount_in_cart" => 0.00
		);
		
		$model_plugin = new Algorithms_Extensions_Plugin();
		
		if($this->item_id)
		{
			$model_products = new Databases_Tables_MateriaProducts();
			$model_products->product_id = $this->item_id;
			$model_products->business_channel_id = 1; //eat-in
			$product_info = $model_products->FetchProductById();
			
			if($product_info)
			{
				if($product_info['product_status'])
				{
					if(0 < $product_info['stock_on_hand'])
					{
						//proceed
						if($_SESSION['eat-in']['items']['products'][$product_info['product_id']]) //update qty
						{
							$_SESSION['eat-in']['items']['products'][$product_info['product_id']][0] += 1;
							$_SESSION['eat-in']['items']['products'][$product_info['product_id']][1] = $model_plugin->FormatPrice($product_info['unit_price'] * $_SESSION['eat-in']['items']['products'][$product_info['product_id']][0]);
						}else{ //add one
							$_SESSION['eat-in']['items']['products'][$product_info['product_id']] = array(1, $product_info['unit_price'], $product_info['product_name'], $product_info['product_category']);
						}
						
						//update order amount
						$_SESSION['eat-in']['payment']['subtotal'] += $product_info['unit_price'];
						$_SESSION['eat-in']['payment']['total'] += $product_info['unit_price'];
						$_SESSION['eat-in']['payment']['subtotal'] = $model_plugin->FormatPrice($_SESSION['eat-in']['payment']['subtotal']);
						$_SESSION['eat-in']['payment']['total'] = $model_plugin->FormatPrice($_SESSION['eat-in']['payment']['total']);
						
						if($_SESSION['eat-in']['payment']['cash'])
						{
							$_SESSION['eat-in']['payment']['change'] = $model_plugin->FormatPrice($_SESSION['eat-in']['payment']['cash'] - $_SESSION['eat-in']['payment']['total']);
						}
						
						//count qty
						if($_SESSION['eat-in']['items']['sets'])
						{
							$qty_sets = count($_SESSION['eat-in']['items']['sets']);
						}else{
							$qty_sets = 0;
						}
						
						$qty_products = 0;
						
						foreach($_SESSION['eat-in']['items']['products'] as $parray)
						{
							$qty_products += $parray[0];
						}
						
						$result = array(
								"qty_in_cart" => $qty_sets + $qty_products,
								"amount_in_cart" => $_SESSION['eat-in']['payment']['total']
						);
						
						$error = 0; // no error
						
					}else{
						$error = 3; //out of stock
					}
				}else{
					$error = 2; //unavailable to sale
				}
			}
		}
		
		return $result;
	}
	
	function RemoveProductFromEatInSession()
	{
		$error = 1; //unknown reason
		$model_plugin = new Algorithms_Extensions_Plugin();
	
		if($this->item_id)
		{
			$model_products = new Databases_Tables_MateriaProducts();
			$model_products->product_id = $this->item_id;
			$model_products->business_channel_id = 1; //eat-in
			$product_info = $model_products->FetchProductById();
			
			if(1 < $_SESSION['eat-in']['items']['products'][$product_info['product_id']][0]) //deduct one
			{
				if($product_info)
				{
					//proceed
					$_SESSION['eat-in']['items']['products'][$product_info['product_id']][0] -= 1;
					$_SESSION['eat-in']['items']['products'][$product_info['product_id']][1] = $model_plugin->FormatPrice($product_info['unit_price'] * $_SESSION['eat-in']['items']['products'][$product_info['product_id']][0]);
							
					$error = 0; // no error
				}
			}else{ //unset directly
				unset($_SESSION['eat-in']['items']['products'][$product_info['product_id']]);
			}
			
			//update order amount
			$_SESSION['eat-in']['payment']['subtotal'] -= $product_info['unit_price'];
			$_SESSION['eat-in']['payment']['total'] -= $product_info['unit_price'];
			$_SESSION['eat-in']['payment']['subtotal'] = $model_plugin->FormatPrice($_SESSION['eat-in']['payment']['subtotal']);
			$_SESSION['eat-in']['payment']['total'] = $model_plugin->FormatPrice($_SESSION['eat-in']['payment']['total']);
			
			if($_SESSION['eat-in']['payment']['cash'])
			{
				$_SESSION['eat-in']['payment']['change'] = $model_plugin->FormatPrice($_SESSION['eat-in']['payment']['cash'] - $_SESSION['eat-in']['payment']['total']);
			}
		}
	
		return $error;
	}
	
	function AddSetsIntoEatInSession()
	{
		$result = array(
				"qty_in_cart" => 0,
				"amount_in_cart" => 0.00
		);
		
		$error = 1; //unknown reason
		$model_plugin = new Algorithms_Extensions_Plugin();
		
		if($this->item_id)
		{
			$model_sets = new Databases_Tables_MateriaSets();
			$model_sets->sets_id = $this->item_id;
			$sets_info = $model_sets->FetchSetsInfo();
			
			$model_sets_details = new Databases_Joins_GetSetsInfo();
			$model_sets_details->sets_id_array = array($this->item_id);
			$model_sets_details->business_channel_id = 1; //eat-in
			$sets_details = $model_sets_details->GetSetsPricesAndStock();
			
			if(!empty($sets_details['contains'][$this->item_id]) && !empty($sets_info))
			{
				if($sets_info['sets_status'])
				{
					if(0 < $sets_details['stock'][$this->item_id])
					{
						//proceed
						$_SESSION['eat-in']['items']['sets'][] = array(
								'sets_id' => $this->item_id,
								'sets_category' => $sets_info['sets_category'],
								'sets_name' => $sets_info['sets_name'],
								'sets_price' => $model_plugin->FormatPrice($sets_details['price'][$this->item_id]),
								'contains' => $sets_details['contains'][$this->item_id]
						);
						
						//update order amount
						foreach($sets_details['contains'][$this->item_id] as $item_details)
						{
							$_SESSION['eat-in']['payment']['subtotal'] += $item_details['unit_price'];
							$_SESSION['eat-in']['payment']['total'] += $item_details['unit_price'];
						}
						
						$_SESSION['eat-in']['payment']['subtotal'] = $model_plugin->FormatPrice($_SESSION['eat-in']['payment']['subtotal']);
						$_SESSION['eat-in']['payment']['total'] = $model_plugin->FormatPrice($_SESSION['eat-in']['payment']['total']);
						
						if($_SESSION['eat-in']['payment']['cash'])
						{
							$_SESSION['eat-in']['payment']['change'] = $model_plugin->FormatPrice($_SESSION['eat-in']['payment']['cash'] - $_SESSION['eat-in']['payment']['total']);
						}
						
						//count qty
						if($_SESSION['eat-in']['items']['sets'])
						{
							$qty_sets = count($_SESSION['eat-in']['items']['sets']);
						}else{
							$qty_sets = 0;
						}
						
						$qty_products = 0;
						
						foreach($_SESSION['eat-in']['items']['products'] as $parray)
						{
							$qty_products += $parray[0];
						}
						
						$result = array(
								"qty_in_cart" => $qty_sets + $qty_products,
								"amount_in_cart" => $_SESSION['eat-in']['payment']['total']
						);
						
						$error = 0; // no error
						
					}else{
						$error = 3; //out of stock
					}
				}
			}
		}
		
		return $result;
	}
	
	function RemoveSetsFromEatInSession()
	{
		$error = 1; //unknown reason
		$model_plugin = new Algorithms_Extensions_Plugin();
	
		if(is_numeric($this->item_id))
		{
			$price = $_SESSION['eat-in']['items']['sets'][$this->item_id]['sets_price'];
			
			//remove in session
			unset($_SESSION['eat-in']['items']['sets'][$this->item_id]);
			
			//update order amount
			$_SESSION['eat-in']['payment']['subtotal'] -= $price;
			$_SESSION['eat-in']['payment']['total'] -= $price;
			$_SESSION['eat-in']['payment']['subtotal'] = $model_plugin->FormatPrice($_SESSION['eat-in']['payment']['subtotal']);
			$_SESSION['eat-in']['payment']['total'] = $model_plugin->FormatPrice($_SESSION['eat-in']['payment']['total']);
			
			if($_SESSION['eat-in']['payment']['cash'])
			{
				$_SESSION['eat-in']['payment']['change'] = $model_plugin->FormatPrice($_SESSION['eat-in']['payment']['cash'] - $_SESSION['eat-in']['payment']['total']);
			}
		}
	
		return $error;
	}
	
	function ErrorReasonMap($error_code)
	{
		switch ($error_code)
		{
			case 1:
				$result = "未知错误";
				break;
			case 2:
				$result = "商品暂停销售";
				break;
			case 3:
				$result = "商品库存不足";
				break;
		}
	}
	
	function CountProductQtyFromSession()
	{
		$result = array();
		
		if($_SESSION['eat-in']['items']['products'])
		{
			foreach($_SESSION['eat-in']['items']['products'] as $products_id => $products_val)
			{
				if(!$result[$products_id] && 0 !== $result[$products_id])
				{
					$result[$products_id] = 0;
				}
				
				$result[$products_id] += $products_val[0];
			}
		}
		
		if($_SESSION['eat-in']['items']['sets'])
		{
			foreach($_SESSION['eat-in']['items']['sets'] as $sets)
			{
				foreach($sets['contains'] as $contains)
				{
					if(!$result[$contains['product_id']] && 0 !== $result[$contains['product_id']])
					{
						$result[$contains['product_id']] = 0;
					}
					
					$result[$contains['product_id']] += $contains['qty'];
				}
			}
		}
		
		return $result;
	}
	
	function RestoreSessionToDeleteOrders()
	{
		$result = FALSE;
		
		if($this->order_id)
		{
			$stock_change = array();
			
			$table_orders = new Databases_Tables_Orders();
			$orders_row = $table_orders->fetchRow("orders_id='".$this->order_id."'");
			$orders_row = $orders_row->toArray();
			if(!empty($orders_row) && 1 == $orders_row['orders_status'])
			{
				$update_data = array("orders_status"=>7);//canceled
				$orders_result = $table_orders->update($update_data, "orders_id='".$this->order_id."'");
				
				if($orders_result)
				{
					//contains
					$table_orders_contains = new Databases_Tables_OrdersContains();
					$orders_contains_rows = $table_orders_contains->fetchAll("orders_id='".$this->order_id."'");
					$orders_contains_rows = $orders_contains_rows->toArray();
					
					if(!empty($orders_contains_rows))
					{
						$orders_contains_id_array = array();
						
						foreach($orders_contains_rows as $orders_contains_row)
						{
							$orders_contains_id_array[] = $orders_contains_row['orders_contains_id'];
						}
						
						$orders_contains_id_string = implode(",", $orders_contains_id_array);
						
						//contains sets
						if(!empty($orders_contains_id_array))
						{
							$table_orders_sets_contains = new Databases_Tables_OrdersSetsContains();
							$orders_sets_contains_rows =$table_orders_sets_contains->fetchAll("orders_contains_id IN (".$orders_contains_id_string.")");
							$orders_sets_contains_rows = $orders_sets_contains_rows->toArray();
						}
					}
				}
			
				/**
				 * $orders_row
				 * $orders_contains_rows
				 * $orders_sets_contains_rows
				 */
					
				//clean session first
				$this->InitialEatInSession();
					
				//restructure
				$_SESSION['eat-in']['payment']['order_id'] = $orders_row['orders_id'];
				$_SESSION['eat-in']['payment']['ctime'] = $orders_row['orders_time'];
				$_SESSION['eat-in']['payment']['subtotal'] = $orders_row['orders_subtotal'];
				$_SESSION['eat-in']['payment']['used_coupon'] = $orders_row['orders_coupon'];
				$_SESSION['eat-in']['payment']['total'] = $orders_row['orders_amount'];
				$_SESSION['eat-in']['payment']['cash'] = $orders_row['orders_amount']; //initial
				$_SESSION['eat-in']['payment']['change'] = 0; //initial
				$_SESSION['eat-in']['payment']['user_code'] = $_SESSION['user_info']['user_code'];
				$_SESSION['eat-in']['payment']['user_alias'] = $_SESSION['user_info']['user_alias'];
				$_SESSION['eat-in']['payment']['member_id'] = $orders_row['orders_member_id'];
				$_SESSION['eat-in']['payment']['table_id'] = $orders_row['table_id'];
					
				if(!empty($orders_contains_rows))
				{
					$mod_categories = new Databases_Tables_MateriaCategories();
					$is_sets = $mod_categories->IsSets();
				
					foreach($orders_contains_rows as $orders_contains_row)
					{
						if(in_array($orders_contains_row['orders_item_category'], $is_sets)) //sets
						{
							//make sets contains
							$a_sets_contains = array();
				
							foreach($orders_sets_contains_rows as $orders_sets_contains_row)
							{
								if($orders_sets_contains_row['orders_contains_id'] == $orders_contains_row['orders_contains_id'])
								{
									$a_sets_contains[$orders_sets_contains_row['materia_contains_id']] = array(
											'product_id' => $orders_sets_contains_row['materia_product_id'],
											'qty' => $orders_sets_contains_row['materia_product_qty'],
											'unit_price' => $orders_sets_contains_row['materia_product_price'],
											'product_name' => $orders_sets_contains_row['materia_product_name'],
											'product_category' => $orders_sets_contains_row['materia_category_id']
									);
				
									$stock_change[$orders_sets_contains_row['materia_product_id']] += $orders_sets_contains_row['materia_product_qty'];
								}
							}
				
							//make contains
							$_SESSION['eat-in']['items']['sets'][] = array(
									'sets_id' => $orders_contains_row['orders_item_id'],
									'sets_category' => $orders_contains_row['orders_item_category'],
									'sets_name' => $orders_contains_row['orders_item_name'],
									'sets_price' => $orders_contains_row['orders_item_price'],
									'contains' => $a_sets_contains
							);
						}else{ //product
							$_SESSION['eat-in']['items']['products'][$orders_contains_row['orders_item_id']] = array(
									0 => $orders_contains_row['orders_item_qty'],
									1 => $orders_contains_row['orders_item_price'],
									2 => $orders_contains_row['orders_item_name'],
									3 => $orders_contains_row['orders_item_category']
							);
				
							$stock_change[$orders_contains_row['orders_item_id']] += $orders_contains_row['orders_item_qty'];
						}
					}
				}
					
				//update stock
				$mod_products = new Databases_Tables_MateriaProducts();
				$mod_products->product_stock_array = $stock_change;
				$mod_products->order_code = $orders_row['orders_code'];
				$mod_products->StockOperation(1); //plus
				
				$result = TRUE;
			}
		}
		
		return $result;
	}
	
	function MakeOrderArrayLikeSession()
	{
		$result = NULL;
		
		if($this->order_id)
		{
			$table_orders = new Databases_Tables_Orders();
			$orders_row = $table_orders->fetchRow("orders_id='".$this->order_id."'");
			$orders_row = $orders_row->toArray();
			if(!empty($orders_row))
			{
				//contains
				$table_orders_contains = new Databases_Tables_OrdersContains();
				$orders_contains_rows = $table_orders_contains->fetchAll("orders_id='".$this->order_id."'");
				$orders_contains_rows = $orders_contains_rows->toArray();
				
				if(!empty($orders_contains_rows))
				{
					$orders_contains_id_array = array();
					
					foreach($orders_contains_rows as $orders_contains_row)
					{
						$orders_contains_id_array[] = $orders_contains_row['orders_contains_id'];
					}
					
					$orders_contains_id_string = implode(",", $orders_contains_id_array);
					
					//contains sets
					if(!empty($orders_contains_id_array))
					{
						$table_orders_sets_contains = new Databases_Tables_OrdersSetsContains();
						$orders_sets_contains_rows =$table_orders_sets_contains->fetchAll("orders_contains_id IN (".$orders_contains_id_string.")");
						$orders_sets_contains_rows = $orders_sets_contains_rows->toArray();
					}
				}
				
				$result = $this->InitialEatInSession(1);
			
				/**
				 * $orders_row
				 * $orders_contains_rows
				 * $orders_sets_contains_rows
				 */
					
				//restructure
				$result['payment']['orders_ref'] = $orders_row['orders_id'];
				$result['payment']['ctime'] = $orders_row['orders_time'];
				$result['payment']['subtotal'] = $orders_row['orders_subtotal'];
				$result['payment']['used_coupon'] = $orders_row['orders_coupon'];
				$result['payment']['total'] = $orders_row['orders_amount'];
				$result['payment']['cash'] = $orders_row['orders_amount']; //initial
				$result['payment']['change'] = 0; //initial
				$result['payment']['users_id'] = $orders_row['users_id'];
				$result['payment']['member_id'] = $orders_row['orders_member_id'];
				$result['payment']['table_id'] = $orders_row['table_id'];
				$result['payment']['orders_type'] = $orders_row['orders_type'];
					
				if(!empty($orders_contains_rows))
				{
					$mod_categories = new Databases_Tables_MateriaCategories();
					$is_sets = $mod_categories->IsSets();
				
					foreach($orders_contains_rows as $orders_contains_row)
					{
						if(in_array($orders_contains_row['orders_item_category'], $is_sets)) //sets
						{
							//make sets contains
							$a_sets_contains = array();
				
							foreach($orders_sets_contains_rows as $orders_sets_contains_row)
							{
								if($orders_sets_contains_row['orders_contains_id'] == $orders_contains_row['orders_contains_id'])
								{
									$a_sets_contains[$orders_sets_contains_row['materia_contains_id']] = array(
											'product_id' => $orders_sets_contains_row['materia_product_id'],
											'qty' => $orders_sets_contains_row['materia_product_qty'],
											'unit_price' => $orders_sets_contains_row['materia_product_price'],
											'product_name' => $orders_sets_contains_row['materia_product_name'],
											'product_category' => $orders_sets_contains_row['materia_category_id']
									);
								}
							}
				
							//make contains
							$result['items']['sets'][] = array(
									'sets_id' => $orders_contains_row['orders_item_id'],
									'sets_category' => $orders_contains_row['orders_item_category'],
									'sets_name' => $orders_contains_row['orders_item_name'],
									'sets_price' => $orders_contains_row['orders_item_price'],
									'contains' => $a_sets_contains
							);
						}else{ //product
							$result['items']['products'][$orders_contains_row['orders_item_id']] = array(
									0 => $orders_contains_row['orders_item_qty'],
									1 => $orders_contains_row['orders_item_price'],
									2 => $orders_contains_row['orders_item_name'],
									3 => $orders_contains_row['orders_item_category']
							);
						}
					}
				}
			}
		}
		
		return $result;
	}
}
?>