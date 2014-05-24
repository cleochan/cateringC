<?php

class IndexController extends Zend_Controller_Action
{
    function indexAction()
    {
    	//get posted data
    	$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
    	$signature = $_GET["signature"];
    	$timestamp = $_GET["timestamp"];
    	$nonce = $_GET["nonce"];
    	
    	//initial for error log
    	$ip = NULL;
    	$note1 = NULL;
    	$note2 = NULL;
		
		//record test log
		$mod_testlog = new Databases_Tables_TestLog();
		$mod_testlog->log_val= $postStr;
		$mod_testlog->AddLog();
		die;
    }
}

