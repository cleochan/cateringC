<?php

class OrdersController extends Zend_Controller_Action
{
	function init()
	{
		$this->db = Zend_Registry::get("db");
	}
	
	function preDispatch()
	{
		//check identity
		$params = $this->_request->getParams();
		$mod_admin = new Databases_Tables_Admin();
		
		if($params['session_id'])
    	{
    		$_SESSION['admin_session'] = $params['session_id'];
    	}
    	
    	if($_SESSION['admin_session']){
    		$mod_params = new Databases_Tables_Params();
    		if($_SESSION['admin_session'] === $mod_params->GetVal('TestSession'))
    		{
    			$fake_admin = $mod_admin->fetchRow("admin_id=2");
    			$admin_info = $fake_admin->toArray();
    		}else{
    			$admin_info = $mod_admin->CheckSessionValidation();
    		}
    		
    		if(empty($admin_info))
    		{
    			$error = 1005;
    		}else{
    			$_SESSION['admin_info'] = $admin_info;
    		}
    	}else{
    		$error = 1002;
    	}
    	
    	//register error
    	if($error)
    	{
    		$mod_error = new Databases_Tables_ErrorLog();
    		$mod_error->error_id = $error;
    		$mod_error->visitor_ip = $_SERVER['REMOTE_ADDR'];
    		$error_msg = $mod_error->AddLog();
    			
    		echo $error_msg;
    		die;
    	}
	}
	
	function indexAction()
    {
    	echo "Invaid Action";
			
		die;
    }
    
    function placeOrderAction()
    {
    	//Fetch category names
    	$get_categories = new Databases_Tables_MateriaCategories();
    	$this->view->categories_array = $categories_array = $get_categories->CategoriesForOrders();
    	
    	//Fetch product and category relations
    	$products = new Databases_Tables_MateriaProducts();
    	$products->business_channel_id = 1;
    	$products->category_id_array = $categories_array['normal'];
    	$this->view->product_array = $products->FetchProductsByCategory();
    	 
    	//Fetch sets and category relations
    	$sets = new Databases_Tables_MateriaSets();
    	$sets->business_channel_id = 1;
    	$sets->category_id_array = $categories_array['sets'];
    	$this->view->sets_array = $sets->FetchProductsByCategory();
    	
    	//Check data in current cart
    	if($_SESSION['eat-in'])
    	{
    		if($_SESSION['eat-in']['items']['sets'])
    		{
    			$qty_sets = count($_SESSION['eat-in']['items']['sets']);
    		}else{
    			$qty_sets = 0;
    		}
    		 
    		$qty_products = 0;
    		 
    		if($_SESSION['eat-in']['items']['products'])
    		{
    			foreach($_SESSION['eat-in']['items']['products'] as $parray)
    			{
    				$qty_products += $parray[0];
    			}
    		}
    		
    		$this->view->current_data = array(
    				"qty_in_cart" => $qty_sets + $qty_products,
    				"amount_in_cart" => $_SESSION['eat-in']['payment']['total']
    		);
    	}
    }
    
    function trashOrderAction()
    {
    	$mod_orders_info_generation = new Algorithms_Core_OrdersInfoGeneration();
    	$mod_orders_info_generation->CleanEatInSession();
    	
    	$this->_redirect("/orders/place-order");
    }
    
    function cartAction()
    {
    	
    }
    
    function updateProductAction()
    {
    	$params = $this->_request->getParams();
    	
    	if($params['id'])
    	{
    		$this->view->product_id = $params['id'];
    	}
    }
    
    function updateProductSubmitAction()
    {
    	$params = $this->_request->getParams();
    	
    	if($params['item_id'])
    	{
    		if(is_numeric($params['qty']))
    		{
    			$mod_order_generation = new Algorithms_Core_OrdersInfoGeneration();
    			$mod_order_generation->item_id = $params['item_id'];
    			$ori_qty = $_SESSION['eat-in']['items']['products'][$params['item_id']][0];
    			
    			if($params['qty'] > $ori_qty)
    			{
    				for($n=1;$n<=($params['qty']-$ori_qty);$n++)
    				{
    					$mod_order_generation->AddProductIntoEatInSession();
    				}
    			}elseif($params['qty'] < $ori_qty){
    				for($n=1;$n<=($ori_qty-$params['qty']);$n++)
    				{
    					$mod_order_generation->RemoveProductFromEatInSession();
    				}
    			}
    		}
    	}
    	
    	$this->_redirect("/orders/cart");
    }
    
    function updateSetsAction()
    {
    	$params = $this->_request->getParams();
    	
    	$this->view->item_id = $params['id'];
    	
    	//analyze replacement
    	if($_SESSION['eat-in']['items']['sets'][$params['id']])
    	{
    		$mod_sets_operation = new Databases_Joins_SetsOperation();
    		$mod_sets_operation->business_channel_id = 1; //堂吃
    		$mod_sets_operation->current_sets_info = $_SESSION['eat-in']['items']['sets'][$params['id']];
    		$this->view->replacement_pool = $mod_sets_operation->FetchReplacements();
    	}
    }
    
    function updateSetsSubmitAction()
    {
    	$params = $this->_request->getParams();
    	
    	if($params['act'])
    	{
    		if('del' == $params['act'])
    		{
    			$mod_orders_info_generation = new Algorithms_Core_OrdersInfoGeneration();
    			$mod_orders_info_generation->item_id = $params['item_id'];
    			$mod_orders_info_generation->RemoveSetsFromEatInSession();
    		}elseif('upd' == $params['act']){
    			$mod_sets_operation = new Databases_Joins_SetsOperation();
    			$mod_sets_operation->business_channel_id = 1; //堂吃
    			$mod_sets_operation->current_sets_info = $_SESSION['eat-in']['items']['sets'][$params['item_id']];
    			$replacement_pool = $mod_sets_operation->FetchReplacements();
    			
    			$mod_sets_operation->item_id = $params['item_id'];
    			$mod_sets_operation->replacement_pool = $replacement_pool;
    			$mod_sets_operation->original_contains_id = $params['conid'];
    			$mod_sets_operation->new_product_id = $params['newpro'];
    			$mod_sets_operation->UpdateSetsInfo();
    		}
    	}
    	
    	$this->_redirect("/orders/cart");
    }
    
    function checkOutAction()
    {
        $params = $this->_request->getParams();
    	
    	//proceed
    	$mod_orders = new Databases_Tables_Orders();
    	
    	$mod_orders->createRow();
    	$mod_orders-> orders_channel = 1; //eat-in
    	$mod_orders-> orders_payment_status = 0; //Unpaid
    	$mod_orders-> orders_type = $params['cotype']; //eat-in
    	$mod_orders-> table_id = $params['table_id'];
    	$mod_orders-> orders_status = 1; //Pending
    	$mod_orders-> orders_time = date("Y-m-d H:i:s");
    	$mod_orders-> orders_amount = $_SESSION['eat-in']['payment']['total'];
    	$mod_orders-> orders_cash = $_SESSION['eat-in']['payment']['cash'];
    	$mod_orders-> orders_change = $_SESSION['eat-in']['payment']['change'];
    	$mod_orders-> orders_subtotal = $_SESSION['eat-in']['payment']['subtotal'];
    	$mod_orders-> orders_coupon = $_SESSION['eat-in']['payment']['used_coupon'];
    	$mod_orders-> orders_discount = $_SESSION['eat-in']['payment']['discount'];
    	$mod_orders-> orders_items = $_SESSION['eat-in']['items'];
    	$result = $mod_orders-> InsertOrder();
    	
    	if($result)
    	{ //success
    		//clean session
    		$eatin_mod = new Algorithms_Core_OrdersInfoGeneration();
    		$eatin_mod->CleanEatInSession();
    		
    		$this->_redirect("/orders/place-order");
    	}else{ //failed
    		echo "下单失败。错误代码001";
    	}

    	die;
    }
    
    function viewStatusAction()
    {
    	$mod_orders_info = new Databases_Joins_OrdersInfo();
    	$this->view->data = $mod_orders_info->DumpLogOnWechat();
    }
}



