<?php
class Databases_Joins_Reports
{
	var $category;
	var $date_from;
	var $date_to;
	var $orderby; //qty, amount
	var $range;
	
	function __construct(){
    	$this->db = Zend_Registry::get("db");
    }
    
    function MakeReportItemsList()
    {
    	$data = $this->db->select();
    	$data->from("report-items as r", array("sum(ritem_qty) as qty", "sum(ritem_price) as price", "ritem_name"));
    	
    	if($this->category)
    	{
    		$data->where("rcategory = ?", $this->category);
    	}
    	
    	if($this->date_from)
    	{
    		$data->where("rdate >= ?", $this->date_from." 00:00:00");
    	}
    	
    	if($this->date_to)
    	{
    		$data->where("rdate <= ?", $this->date_to." 23:59:59");
    	}
    	
    	$data->group("ritem_name");
    	
    	if('amount' == $this->orderby)
    	{
    		$data->order("price DESC");
    	}else{
    		$data->order("qty DESC");
    	}
    	
    	$rows = $this->db->fetchAll($data);
    	
    	return $rows;
    }
    
    function MakeReportRevList($range)
    {
    	$result_key = array();
    	$result_val = array();
    	$result_hour_key = array();
    	$result_hour_val = array();
    	
    	if('day' == $range)
    	{
    		$data = $this->db->select();
    		$data->from("report-rev", array("date(order_time) as dt", "sum(order_amount) as amount"));
    		$data->where("order_time >= ?", date("Y-m-d", mktime(0,0,0,date("m")-1,date("d"),date("Y"))));
    		$data->group("dt");
    		$data->order("dt ASC");
    		$rows = $this->db->fetchAll($data);
    		
    		if(!empty($rows))
    		{
    			foreach($rows as $row)
    			{
    				$result_key[] = date("D", mktime(0,0,0,substr($row['dt'],5,2),substr($row['dt'],8,2),substr($row['dt'],0,4)));
    				$result_val[] = $row['amount'];
    			}
    		}
    		
    		$data = $this->db->select();
    		$data->from("report-rev", array("hour(order_time) as dt", "sum(order_amount) as amount"));
    		$data->where("order_time >= ?", date("Y-m-d", mktime(0,0,0,date("m")-1,date("d"),date("Y"))));
    		$data->group("dt");
    		$data->order("dt ASC");
    		$rows = $this->db->fetchAll($data);
    		
    		if(!empty($rows))
    		{
    			foreach($rows as $row)
    			{
    				$result_hour_key[] = $row['dt'];
    				$result_hour_val[] = $row['amount'];
    			}
    		}
    	}elseif('week' == $range)
    	{
    		$data = $this->db->select();
    		$data->from("report-rev", array("week(order_time) as dt", "sum(order_amount) as amount"));
    		$data->where("order_time >= ?", date("Y-m-d", mktime(0,0,0,date("m")-12,date("d"),date("Y"))));
    		$data->group("dt");
    		$data->order("dt ASC");
    		$rows = $this->db->fetchAll($data);
    		
    		if(!empty($rows))
    		{
    			foreach($rows as $row)
    			{
    				$result_key[] = $row['dt'];
    				$result_val[] = $row['amount'];
    			}
    		}
    		
    		$data = $this->db->select();
    		$data->from("report-rev", array("hour(order_time) as dt", "sum(order_amount) as amount"));
    		$data->where("order_time >= ?", date("Y-m-d", mktime(0,0,0,date("m")-12,date("d"),date("Y"))));
    		$data->group("dt");
    		$data->order("dt ASC");
    		$rows = $this->db->fetchAll($data);
    		
    		if(!empty($rows))
    		{
    			foreach($rows as $row)
    			{
    				$result_hour_key[] = $row['dt'];
    				$result_hour_val[] = $row['amount'];
    			}
    		}
    	}elseif('month' == $range)
    	{
    		$data = $this->db->select();
    		$data->from("report-rev", array("month(order_time) as dt", "sum(order_amount) as amount"));
    		$data->where("order_time >= ?", date("Y-m-d", mktime(0,0,0,date("m")-12,date("d"),date("Y"))));
    		$data->group("dt");
    		$data->order("dt ASC");
    		$rows = $this->db->fetchAll($data);
    		
    		if(!empty($rows))
    		{
    			foreach($rows as $row)
    			{
    				$result_key[] = $row['dt'];
    				$result_val[] = $row['amount'];
    			}
    		}
    		
    		$data = $this->db->select();
    		$data->from("report-rev", array("hour(order_time) as dt", "sum(order_amount) as amount"));
    		$data->where("order_time >= ?", date("Y-m-d", mktime(0,0,0,date("m")-12,date("d"),date("Y"))));
    		$data->group("dt");
    		$data->order("dt ASC");
    		$rows = $this->db->fetchAll($data);
    		
    		if(!empty($rows))
    		{
    			foreach($rows as $row)
    			{
    				$result_hour_key[] = $row['dt'];
    				$result_hour_val[] = $row['amount'];
    			}
    		}
    	}
    	
    	$result['result_key'] = implode('","', $result_key);
    	$result['result_val'] = implode(",", $result_val);
    	$result['result_hour_key'] = implode('","', $result_hour_key);
    	$result['result_hour_val'] = implode(",", $result_hour_val);
    	
    	return $result;
    }
}




