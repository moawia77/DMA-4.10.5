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
**      Result: true if API succeeded or false
*****************************************************************************/
function sendsms($recp, $body, &$errmsg)
{
  // enter your clickatell.com credentials here

  $api_key = "clickatell_api_key";

  // clean up HTML entities

  $body = html_entity_decode($body, ENT_COMPAT, "UTF-8");

  // uncomment the following lines to see the SMS details

//  print $recp . "<br>";
//  print $body . "<br>";
//  return true;

  // no recipient set, return false
  if (!$recp)
  {
    $errmsg = "Invalid cell number";
    return false;
  }

  // implement your own SMS gateway here
  
  $body = rawurlencode($body);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "https://platform.clickatell.com/messages/http/send?apiKey=$api_key&to=$recp&content=$body");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  $res = curl_exec($ch);

  // CURL error?
  if ($res === false)
  {
    $errmsg = 'Curl error: ' . curl_error($ch);
    curl_close($ch);
    return false;
  }

  curl_close($ch);

  // process results
  if (strpos($res, "accepted") )
    $res_str = "OK";
  else
    $res_str = $res;

  // log results
  syslog(LOG_INFO, "[radiusmanager] Sending SMS to " . $recp . " (" . $res_str . ")");

  // error has occured, return false
  if ($res_str != "OK")
  {
    $errmsg = $res_str;
    return false;
  }

  // no error, return true
  return true;
}
?>
