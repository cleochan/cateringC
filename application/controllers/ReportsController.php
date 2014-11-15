<?php

class ReportsController extends Zend_Controller_Action
{
    function init()//
    {
        $this->db = Zend_Registry::get("db");
    }
    
    function preDispatch()
    {
        //get system_name
        $get_title = new Databases_Tables_Params();
        $this->view->system_name = $get_title -> GetVal("system_name");
    }
    
    function indexAction()
    {
    	$this->_redirect("/reports/rev");
    }
    
    function siAction()
    {
    	$params = $this->_request->getParams();
    	
    	$mod_reports = new Databases_Joins_Reports();
    	
    	$url_param = array();
    	
    	if($params['date_from'])
    	{
    		$date_from = $params['date_from'];
    	}else{
    		$date_from = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));
    	}
    	
    	$mod_reports->date_from = $date_from;
    	$this->view->date_from = $date_from;
    	
    	if($params['date_to'])
    	{
    		$data_to = $params['date_to'];
    	}else{
    		$date_to = date("Y-m-d", time());
    	}
    	
    	$mod_reports->date_to = $date_to;
    	$this->view->date_to = $date_to;
    	
    	if($params['category'] || 0 == $params['category'])
    	{
    		$mod_reports->category = $params['category'];
    		$this->view->category = $params['category'];
    	}
    	
    	if($params['orderby'])
    	{
    		$mod_reports->orderby = $params['orderby'];
    		$this->view->orderby = $params['orderby'];
    	}
    	
    	$this->view->data = $mod_reports->MakeReportItemsList();
    	
    	//category names
    	$mod_categories = new Databases_Tables_MateriaCategories();
    	$this->view->categories = $mod_categories->DumpAll();
    	
    	//make pie
    	$pie = $mod_reports->MakeCategoriesPie();
    	 
    	$pie_array = array();
    	 
    	if(!empty($pie))
    	{
    		$amount = 0;
    	
    		foreach($pie as $pie_val)
    		{
    			$amount += $pie_val['price'];
    		}
    	
    		foreach($pie as $pie_val)
    		{
    			$percent = number_format($pie_val['price'] / $amount * 100, 1, '.', '');
    			 
    			$pie_array[] = "{label: '".$pie_val['category_name']." ".$percent."%',value:".$pie_val['price'].",color:'#".$pie_val['category_color']."'}";
    		}
    	}
    	 
    	$this->view->pie = implode(",", $pie_array);
    }
    
    function revAction()
    {
    	$params = $this->_request->getParams();
    	
    	$mod_reports = new Databases_Joins_Reports();
    	
    	if(!$params['range'])
    	{
    		$params['range'] = 'day';
    	}
    	
    	$this->view->data = $mod_reports->MakeReportRevList($params['range']);
    }
}

