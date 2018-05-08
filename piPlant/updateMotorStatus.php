<?php
    date_default_timezone_set('Asia/Kolkata');
    $str = file_get_contents('/var/www/html/api/piPlant/data.json');
    $json = json_decode($str, true);
   
    
    $host = $json['host'];
    $userName = $json['userName'];
    $password = $json['password'];
    $db = $json['db'];
    $soil = $json['soil'];
    $room = $json['room'];
    $water = $json['water'];

    $dbhost="localhost";
    $dbuser=$userName;
    $dbpass=$password;
    $dbname=$db;


$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
$allUser = "SELECT * from updateMotorCron where cronStatus = 0";

$result = mysqli_query($conn,$allUser);

while($data = mysqli_fetch_assoc($result))
{
	
	

    $userId = $data['userId'];
    $userSql = "SELECT * from register where userId != '$userId'";
   
  
        $message = $data['message'];
        $title = $data['title'];
        $resultS = mysqli_query($conn,$userSql);

      while($chkAllData = mysqli_fetch_assoc($resultS))
        {
	
        send_gcm_notify($chkAllData['deviceToken'],$message,$title,$dbhost);
	
        }

      $ids = $data['id'];
	$upd = "update updateMotorCron SET cronStatus = 1 where id = $ids";
	$result = mysqli_query($conn,$upd);
	

}



function send_gcm_notify($devicetoken,$message,$title,$ip = 0)
{

    if (!defined('FIREBASE_API_KEY')) define("FIREBASE_API_KEY", "AAAAyWReL-M:APA91bGEYqULDMblKQg40gmz6n6uqTJG7rsKVi1E37Rm1Qal682L7pRrfa8B1nbb--6JtxLqDaerUpqF02MRXmNDLfQwpRV2YrySiOB9UiCWekVa20piiX1hzFVYiKH4qpPv3CEV18sw");
        if (!defined('FIREBASE_FCM_URL')) define("FIREBASE_FCM_URL", "https://fcm.googleapis.com/fcm/send");

#$me = html_entity_decode($message,ENT_HTML5);
            $fields = array(
                'to' => $devicetoken ,
                'priority' => "high",
                'notification' => array( "tag"=>"chat", "title"=>$title,"body" =>$message,"ip"=>$ip,"priority"=>"high"),
            );

            $headers = array(
                'Authorization: key=' . FIREBASE_API_KEY,
                'Content-Type: application/json'
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, FIREBASE_FCM_URL);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

            

            $result = curl_exec($ch);
            echo $result;
            curl_close($ch);

}