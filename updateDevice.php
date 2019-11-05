<?php

  // The URL to POST to

  $url = "http://52.34.89.223/piplant/api/updateVersion";

$content = file_get_contents('/var/www/html/api/README.md');
$exploadContent = explode("#",$content);
$exploadContent[2];
  // Build the cURL session
  $ch = curl_init();
  
  /*curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, TRUE);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $mySOAP);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);*/

$payload = json_encode( array( "api"=> $exploadContent[1]?$exploadContent[1]:0 ,"ip"=> $exploadContent[2]?$exploadContent[2]:0) );

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,$payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);



  // Send the request and check the response
  if (($result = curl_exec($ch)) === FALSE) 
  {
    die('cURL error: '.curl_error($ch)."<br />\n");
  } else 
  {
	
    echo "Success!<br />\n";
  }
  $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  
echo 'HTTP code: ' . $result;
?>