<?php

class Algorithms_Extensions_Plugin
{
    function FormatArray($array)
    {
        echo "<pre>";
        print_r($array);
        echo "<pre>";
    }
    
    function EmailCheck($email)
    {
        $ret=false;

        if(strstr($email, '@') && strstr($email, '.'))
        {
            if(eregi("^([_a-z0-9]+([._a-z0-9-]+)*)@([a-z0-9]{2,}(.[a-z0-9-]{2,})*.[a-z]{2,3})$", $email))
            {
                $ret=true;
            }
        }

        return $ret;
    }
    
    function GetIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        
        return $ip;
    }
    
    function FormatPrice($price)
    {
    	return number_format($price, 2, '.', ',');
    }
    
    function CellPhoneCheck($phone)
    {
    	if(11 == strlen($phone) && '1' == substr($phone, 0, 1))
    	{
    		$result = 1;
    	}else{
    		$result = 0;
    	}
    	
    	return $result;
    }
    
    function MakeToken()
    {
    	$mod_params = new Databases_Tables_Params();
    	$username = $mod_params->GetVal('CateringAUsername');
    	$password = $mod_params->GetVal('CateringAPassword');
    	$token_key = $mod_params->GetVal('CateringATokenKey');
    	 
    	$token = sha1($password.$token_key.$username);
    	 
    	return array(
    		'username' => $username,
    		'token' => $token
    	);
    }
}