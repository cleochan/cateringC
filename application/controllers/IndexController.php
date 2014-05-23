<?php

class IndexController extends Zend_Controller_Action
{
    function indexAction()
    {
    	$mod_test = new Algorithms_Extensions_Test();
    	$mod_test->valid();
    	
    	echo "<br /><br />End.</br />";
		die;
    }
}

