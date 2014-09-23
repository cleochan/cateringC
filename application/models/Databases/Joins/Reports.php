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
    				$result_key[] = substr($row['dt'],5,5);
    				$result_val[] = $row['amount'];
    			}
    		}
    
    		//按时段
    		$data = $this->db->select();
    		$data->from("report-rev", array("order_time", "order_amount"));
    		$data->where("order_time >= ?", date("Y-m-d", mktime(0,0,0,date("m")-1,date("d"),date("Y"))));
    		$rows = $this->db->fetchAll($data);
    
    		/*
    		 * breakfast 00:00 - 10:29
    		* lunch 10:30 - 13:59
    		* afternoon 14:00 - 16:59
    		* dinner 17:00 - 19:59
    		* night 20:00 - 23:59
    		*/
    		$result_breakfast_key = array();
    		$result_breakfast_val = array();
    		$result_lunch_key = array();
    		$result_lunch_val = array();
    		$result_afternoon_key = array();
    		$result_afternoon_val = array();
    		$result_dinner_key = array();
    		$result_dinner_val = array();
    		$result_night_key = array();
    		$result_night_val = array();
    
    		$breakfast = array();
    		$lunch = array();
    		$afternoon = array();
    		$dinner = array();
    		$night = array();
    
    		if(!empty($rows))
    		{
    			foreach($rows as $row)
    			{
    				$hour = substr($row['order_time'],11,2);
    				$min = substr($row['order_time'],14,2);
    
    				$day = substr($row['order_time'],5,5);
    
    				if($hour < 10 || ($hour==10 && $min < 30))
    				{
    					if(!$breakfast[$day])
    					{
    						$breakfast[$day] = 0;
    					}
    						
    					$breakfast[$day] += $row['order_amount'];
    						
    				}elseif(($hour==10 && $min >= 30) || in_array($hour, array(11,12,13)))
    				{
    					if(!$lunch[$day])
    					{
    						$lunch[$day] = 0;
    					}
    						
    					$lunch[$day] += $row['order_amount'];
    						
    				}elseif(in_array($hour, array(14,15,16)))
    				{
    					if(!$afternoon[$day])
    					{
    						$afternoon[$day] = 0;
    					}
    						
    					$afternoon[$day] += $row['order_amount'];
    						
    				}elseif(in_array($hour, array(17,18,19)))
    				{
    					if(!$dinner[$day])
    					{
    						$dinner[$day] = 0;
    					}
    						
    					$dinner[$day] += $row['order_amount'];
    						
    				}elseif(in_array($hour, array(20,21,22,23)))
    				{
    					if(!$night[$day])
    					{
    						$night[$day] = 0;
    					}
    						
    					$night[$day] += $row['order_amount'];
    						
    				}
    			}
    			 
    			if(!empty($breakfast))
    			{
    				foreach($breakfast as $breakfast_key => $breakfast_val)
    				{
    					$result_breakfast_key[] = $breakfast_key;
    					$result_breakfast_val[] = $breakfast_val;
    				}
    			}
    			 
    			if(!empty($lunch))
    			{
    				foreach($lunch as $lunch_key => $lunch_val)
    				{
    					$result_lunch_key[] = $lunch_key;
    					$result_lunch_val[] = $lunch_val;
    				}
    			}
    			 
    			if(!empty($afternoon))
    			{
    				foreach($afternoon as $afternoon_key => $afternoon_val)
    				{
    					$result_afternoon_key[] = $afternoon_key;
    					$result_afternoon_val[] = $afternoon_val;
    				}
    			}
    			 
    			if(!empty($dinner))
    			{
    				foreach($dinner as $dinner_key => $dinner_val)
    				{
    					$result_dinner_key[] = $dinner_key;
    					$result_dinner_val[] = $dinner_val;
    				}
    			}
    			 
    			if(!empty($night))
    			{
    				foreach($night as $night_key => $night_val)
    				{
    					$result_night_key[] = $night_key;
    					$result_night_val[] = $night_val;
    				}
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
    		$data->from("report-rev", array("week(order_time,1) as dt", "sum(order_amount) as amount"));
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
    
    		//按时段
    		$data = $this->db->select();
    		$data->from("report-rev", array("order_time", "order_amount"));
    		$data->where("order_time >= ?", date("Y-m-d", mktime(0,0,0,date("m")-12,date("d"),date("Y"))));
    		$rows = $this->db->fetchAll($data);
    
    		/*
    		 * breakfast 00:00 - 10:29
    		* lunch 10:30 - 13:59
    		* afternoon 14:00 - 16:59
    		* dinner 17:00 - 19:59
    		* night 20:00 - 23:59
    		*/
    		$result_breakfast_key = array();
    		$result_breakfast_val = array();
    		$result_lunch_key = array();
    		$result_lunch_val = array();
    		$result_afternoon_key = array();
    		$result_afternoon_val = array();
    		$result_dinner_key = array();
    		$result_dinner_val = array();
    		$result_night_key = array();
    		$result_night_val = array();
    
    		$breakfast = array();
    		$lunch = array();
    		$afternoon = array();
    		$dinner = array();
    		$night = array();
    
    		if(!empty($rows))
    		{
    			foreach($rows as $row)
    			{
    				$hour = substr($row['order_time'],11,2);
    				$min = substr($row['order_time'],14,2);
    
    				$week = date("W", mktime(0,0,0,substr($row['order_time'],5,2),substr($row['order_time'],8,2),substr($row['order_time'],0,4)));
    
    				if($hour < 10 || ($hour==10 && $min < 30))
    				{
    					if(!$breakfast[$week])
    					{
    						$breakfast[$week] = 0;
    					}
    
    					$breakfast[$week] += $row['order_amount'];
    
    				}elseif(($hour==10 && $min >= 30) || in_array($hour, array(11,12,13)))
    				{
    					if(!$lunch[$week])
    					{
    						$lunch[$week] = 0;
    					}
    
    					$lunch[$week] += $row['order_amount'];
    
    				}elseif(in_array($hour, array(14,15,16)))
    				{
    					if(!$afternoon[$week])
    					{
    						$afternoon[$week] = 0;
    					}
    
    					$afternoon[$week] += $row['order_amount'];
    
    				}elseif(in_array($hour, array(17,18,19)))
    				{
    					if(!$dinner[$week])
    					{
    						$dinner[$week] = 0;
    					}
    
    					$dinner[$week] += $row['order_amount'];
    
    				}elseif(in_array($hour, array(20,21,22,23)))
    				{
    					if(!$night[$week])
    					{
    						$night[$week] = 0;
    					}
    
    					$night[$week] += $row['order_amount'];
    
    				}
    			}
    
    			if(!empty($breakfast))
    			{
    				foreach($breakfast as $breakfast_key => $breakfast_val)
    				{
    					$result_breakfast_key[] = $breakfast_key;
    					$result_breakfast_val[] = $breakfast_val;
    				}
    			}
    
    			if(!empty($lunch))
    			{
    				foreach($lunch as $lunch_key => $lunch_val)
    				{
    					$result_lunch_key[] = $lunch_key;
    					$result_lunch_val[] = $lunch_val;
    				}
    			}
    
    			if(!empty($afternoon))
    			{
    				foreach($afternoon as $afternoon_key => $afternoon_val)
    				{
    					$result_afternoon_key[] = $afternoon_key;
    					$result_afternoon_val[] = $afternoon_val;
    				}
    			}
    
    			if(!empty($dinner))
    			{
    				foreach($dinner as $dinner_key => $dinner_val)
    				{
    					$result_dinner_key[] = $dinner_key;
    					$result_dinner_val[] = $dinner_val;
    				}
    			}
    
    			if(!empty($night))
    			{
    				foreach($night as $night_key => $night_val)
    				{
    					$result_night_key[] = $night_key;
    					$result_night_val[] = $night_val;
    				}
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
    
    		//按时段
    		$data = $this->db->select();
    		$data->from("report-rev", array("order_time", "order_amount"));
    		$data->where("order_time >= ?", date("Y-m-d", mktime(0,0,0,date("m")-12,date("d"),date("Y"))));
    		$rows = $this->db->fetchAll($data);
    
    		/*
    		 * breakfast 00:00 - 10:29
    		* lunch 10:30 - 13:59
    		* afternoon 14:00 - 16:59
    		* dinner 17:00 - 19:59
    		* night 20:00 - 23:59
    		*/
    		$result_breakfast_key = array();
    		$result_breakfast_val = array();
    		$result_lunch_key = array();
    		$result_lunch_val = array();
    		$result_afternoon_key = array();
    		$result_afternoon_val = array();
    		$result_dinner_key = array();
    		$result_dinner_val = array();
    		$result_night_key = array();
    		$result_night_val = array();
    
    		$breakfast = array();
    		$lunch = array();
    		$afternoon = array();
    		$dinner = array();
    		$night = array();
    
    		if(!empty($rows))
    		{
    			foreach($rows as $row)
    			{
    				$hour = substr($row['order_time'],11,2);
    				$min = substr($row['order_time'],14,2);
    
    				$month = substr($row['order_time'],5,2);
    
    				if($hour < 10 || ($hour==10 && $min < 30))
    				{
    					if(!$breakfast[$month])
    					{
    						$breakfast[$month] = 0;
    					}
    
    					$breakfast[$month] += $row['order_amount'];
    
    				}elseif(($hour==10 && $min >= 30) || in_array($hour, array(11,12,13)))
    				{
    					if(!$lunch[$month])
    					{
    						$lunch[$month] = 0;
    					}
    
    					$lunch[$month] += $row['order_amount'];
    
    				}elseif(in_array($hour, array(14,15,16)))
    				{
    					if(!$afternoon[$month])
    					{
    						$afternoon[$month] = 0;
    					}
    
    					$afternoon[$month] += $row['order_amount'];
    
    				}elseif(in_array($hour, array(17,18,19)))
    				{
    					if(!$dinner[$month])
    					{
    						$dinner[$month] = 0;
    					}
    
    					$dinner[$month] += $row['order_amount'];
    
    				}elseif(in_array($hour, array(20,21,22,23)))
    				{
    					if(!$night[$month])
    					{
    						$night[$month] = 0;
    					}
    
    					$night[$month] += $row['order_amount'];
    
    				}
    			}
    
    			if(!empty($breakfast))
    			{
    				foreach($breakfast as $breakfast_key => $breakfast_val)
    				{
    					$result_breakfast_key[] = $breakfast_key;
    					$result_breakfast_val[] = $breakfast_val;
    				}
    			}
    
    			if(!empty($lunch))
    			{
    				foreach($lunch as $lunch_key => $lunch_val)
    				{
    					$result_lunch_key[] = $lunch_key;
    					$result_lunch_val[] = $lunch_val;
    				}
    			}
    
    			if(!empty($afternoon))
    			{
    				foreach($afternoon as $afternoon_key => $afternoon_val)
    				{
    					$result_afternoon_key[] = $afternoon_key;
    					$result_afternoon_val[] = $afternoon_val;
    				}
    			}
    
    			if(!empty($dinner))
    			{
    				foreach($dinner as $dinner_key => $dinner_val)
    				{
    					$result_dinner_key[] = $dinner_key;
    					$result_dinner_val[] = $dinner_val;
    				}
    			}
    
    			if(!empty($night))
    			{
    				foreach($night as $night_key => $night_val)
    				{
    					$result_night_key[] = $night_key;
    					$result_night_val[] = $night_val;
    				}
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
    	$result['result_breakfast_key'] = implode('","', $result_breakfast_key);
    	$result['result_breakfast_val'] = implode(",", $result_breakfast_val);
    	$result['result_lunch_key'] = implode('","', $result_lunch_key);
    	$result['result_lunch_val'] = implode(",", $result_lunch_val);
    	$result['result_afternoon_key'] = implode('","', $result_afternoon_key);
    	$result['result_afternoon_val'] = implode(",", $result_afternoon_val);
    	$result['result_dinner_key'] = implode('","', $result_dinner_key);
    	$result['result_dinner_val'] = implode(",", $result_dinner_val);
    	$result['result_night_key'] = implode('","', $result_night_key);
    	$result['result_night_val'] = implode(",", $result_night_val);
    	 
    	return $result;
    }
}




