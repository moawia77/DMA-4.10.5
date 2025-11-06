<?php
/*****************************************************************************
**        Name: sendsms
**    Function: This function is used to send SMS message to a mobile phone.
**		The default implementation uses clickatell.com HTTP->SMS gateway.
**		You can implement your own SMS gateway in this function easily.
**		This is the new style clickatell API glue code
**      Inputs: $recp - Mobile number
**		$body - Message body
**		$errmsg - Pointer to error message returned by the gateway
**      Result: TRUE if API succeeded or FALSE
*****************************************************************************/
function sendsms($recp, $body, &$errmsg)
{
  // enter your clickatell.com credentials here

  

  // clean up HTML entities

  $body = html_entity_decode($body, ENT_COMPAT, "UTF-8");

  // uncomment the following lines to see the SMS details
//  print $recp . "<br>";
//  print $body . "<br>";
//  return true;

  // no recipient set, return FALSE
  if (!$recp)
  {
    $errmsg = "Invalid cell number";
    return FALSE;
  }

  // implement your own SMS gateway here
  
  
  $parts = explode("_", $body);
    $sname = $parts[0];
    $susername = $parts[1];
	$number = $recp;
	$sexpire = $parts[2];
	$sdays = $parts[3];
	$saddress = $parts[4];
	
	$var1 = array('type' => 'text', 'parameter_name' => 's_fullname', 'text' => $sname);
  $var2 = array('type' => 'text', 'parameter_name' => 's_username', 'text' => $susername);
  $var3 = array('type' => 'text', 'parameter_name' => 's_expiry', 'text' => $sexpire);
  $var4 = array('type' => 'text', 'parameter_name' => 's_days', 'text' => $sdays);
  $var5 = array('type' => 'text', 'parameter_name' => 's_address', 'text' => $saddress);
     $template = array(
       'name'=>'auto_pay_reminder_2', //your your own or any default template. The names and samples are listed under message templates
       'language'=>array('code'=>'en_US'), //you can use yours
	  'components'=>array(array('type' => 'body', 'parameters' =>array($var1,$var2,$var3,$var4,$var5))) //you can use yours
	   
       );
	   $json = json_encode($template); 
	  // echo implode( ", ", $template );


     $endpoint = 'https://graph.facebook.com/v22.0/563902273479632/messages';
     $params = array('messaging_product'=>'whatsapp', 'to'=>$number, 'type'=>'template', 'access_token'=>'YOURACCESSCODETAKENFROMWHATSAPPAPICLOUD','template'=>json_encode($template)); //get access token from whatsapp cloud

       $headers = array('Authorization'=>'YOURACCESSCODETAKENFROMWHATSAPPAPICLOUD','Content-Type'=>'application/json', 'User-Agent'=>'(Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36'); //get access token from whatsapp cloud and add here in authorization shoud be same as above
       $url = $endpoint . '?' . http_build_query($params);
  //echo $params.'<br>';
       $ch = curl_init();
       curl_setopt( $ch,CURLOPT_URL, $endpoint);
       curl_setopt( $ch,CURLOPT_POST, true );
       curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
       curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
       curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
       curl_setopt( $ch,CURLOPT_POSTFIELDS, $params);
       $res = curl_exec($ch );
       //echo $res; //you can skip this, I did it to check the results
       curl_close( $ch );
	
	
	
  // uncomment the following line to see the result from the SMS gateway

//  print $res;

  // process results
  if (strpos($res, "accepted") )
    $res_str = "OK";
  else
    $res_str = $res;

  // log results
  syslog(LOG_INFO, "[radiusmanager] Sending SMS to this is parts 0 " . $parts[0] . "  and this is body " . $body . " and number " . $recp . " (" . $res_str . ")");

  // error has occured, return FALSE
  if ($res_str != "OK")
  {
    $errmsg = $res_str;
    return FALSE;
  }

  // no error, return TRUE
  return TRUE;
}
?>
