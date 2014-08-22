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
    		$mod_reports->date_from = $params['date_from'];
    		$this->view->date_from = $params['date_from'];
    	}
    	
    	if($params['date_to'])
    	{
    		$mod_reports->date_to = $params['date_to'];
    		$this->view->date_to = $params['date_to'];
    	}
    	
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

