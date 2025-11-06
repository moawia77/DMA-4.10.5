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
  // set your bulksms credentials here

  $username = 'xxx';
  $password = 'xxx';

  // clean up HTML entities

  $body = html_entity_decode($body, ENT_COMPAT, "UTF-8");

  // uncomment the following lines to see the SMS details

//  print $recp . "<br>";
//  print $body . "<br>";

  // no recipient set, return FALSE
  if (!$recp)
  {
    $errmsg = "Invalid cell number";
    return FALSE;
  }

  // implement your own SMS gateway here
  
  $messages = array(
    array('to'=>$recp, 'body'=>rawurlencode($body))
  );

  $result = send_smsmessage(json_encode($messages), 'https://api.bulksms.com/v1/messages?auto-unicode=true&longMessageMaxParts=30', $username, $password);
  $res = json_decode($result['server_response'], TRUE);

  if ($result['http_status'] != 201) // HTTP error
    $res_str = $res['title'] . ': ' . $res['detail'];
  else // HTTP ok
    $res_str = $res[0]['status']['type'];

  // log results
  syslog(LOG_INFO, "[radiusmanager] Sending SMS to " . $recp . " (" . $res_str . ")");

  // error has occured, return FALSE
  if ($res_str != "ACCEPTED")
  {
    $errmsg = $res_str;
    return FALSE;
  }

  // no error, return TRUE
  return TRUE;
}

function send_smsmessage($post_body, $url, $username, $password)
{
  $ch = curl_init();
  $headers = array(
    'Content-Type:application/json',
    'Authorization:Basic '. base64_encode("$username:$password")
  );

  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
  curl_setopt($ch, CURLOPT_TIMEOUT, 20);		// Allow cUrl functions 20 seconds to execute
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);		// Wait 10 seconds while trying to connect
  $output = array();
  $output['server_response'] = curl_exec($ch);
  $curl_info = curl_getinfo($ch);
  $output['http_status'] = $curl_info['http_code'];
  $output['error'] = curl_error($ch);
  curl_close($ch);
  return $output;
}
?>
