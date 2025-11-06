<?php
/*****************************************************************************
**        Name: sendsms
**    Function: This function is used to send Whatsapp message to a mobile phone.
**		You can implement your own SMS gateway in this function easily.
**      Inputs: $recp - Mobile number
**		$body - Message body
**		$errmsg - Pointer to error message returned by the gateway
**      Result: TRUE if API succeeded or FALSE
*****************************************************************************/
function sendsms($recp, $body, &$errmsg)
{
  $url = 'https://graph.facebook.com/v22.0/607636725764901/messages';

  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  
  $headers = array(
     'Authorization: Bearer EAAJYRSwiQREBO4VkIRLD7OvObiJSb1ZAq3OkwmF2UZACNkv9ZCBh1yDbKHQf0CkvmbY64vVHdqnjgeoXskz0CC47Ljgs5T34tXVUHfQ11ZBO6YGzJzVdNWbv4PFxlHVpNNDA59G2tfE4cy3oVtsk8pc7IdzsawMRXLoQGZBUvZBi1iG6mQh1MZCusZACTYamZCP4m',
     'Content-Type: application/json',
  );
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  
  $data = "{'messaging_product': 'whatsapp',
  	    'recipient_type': 'individual',
  	    'to': '$recp',
  	    'type': 'text',
  	    'text': {'body': '$body'}
  	   }";

  curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
  
  //for debug only!
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  
  $resp = curl_exec($curl);
  curl_close($curl);
//  var_dump($resp);
  return TRUE;
}
?>
