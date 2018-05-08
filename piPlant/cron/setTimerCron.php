<?php
    date_default_timezone_set('Asia/Kuwait');
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
$allUser = "SELECT * from setTimerCron where cronStatus = 0";

$result = mysqli_query($conn,$allUser);

while($data = mysqli_fetch_assoc($result))
{
	
	

    $userId = $data['userId'];
    
   
  
        $message = $data['message'];
        $title = $data['title'];
	$ids = $data['cronId'];

	notify($userId,$ids,$message,$title,$conn,$dbhost);
	

}

function notify($userId,$ids,$message,$title,$conn,$dbhost)
{
	
	$userSql = "SELECT * from register where userId != $userId";
        $resultS = mysqli_query($conn,$userSql);

      while($chkAllData = mysqli_fetch_assoc($resultS))
        {
	
        send_gcm_notify($chkAllData['deviceToken'],$message,$title,$dbhost);
	
        }

      
	$upd = "update setTimerCron SET cronStatus = 1 where cronId = $ids";
	$result = mysqli_query($conn,$upd);
}


function send_gcm_notify($devicetoken,$message,$title,$ip = 0)
{

	
    if (!defined('FIREBASE_API_KEY')) define("FIREBASE_API_KEY", "AAAAyWReL-M:APA91bGj2Xvo09h3t_31FX8CppXx2-qhLZnOUUD3mIMhcKTPvVWgQbpSXVSP9OhFccTZLIzFVelP7s_xf3WXuueBWpm5A_h7-e4avkrFJpkjSDNMJDnPg8txofEMQybW8uYUcHD6-L5T");
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
           
            curl_close($ch);

}