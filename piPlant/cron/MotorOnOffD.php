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
 $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 




    
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

$autoOn = "SELECT * from timer join motorName on motorName.motorId = timer.motorId  where timer.status = 1";
$autoOff = "SELECT * from timer join motorName on motorName.motorId = timer.motorId where timer.status  = 1";
$userSql = "SELECT * from register where genNoti = 1";





$result = mysqli_query($conn,$autoOn);

while($data = mysqli_fetch_assoc($result))
{
     $timer = $data;
	$now = date("H:i:00");
	

	/*$json = mysqli_fetch_all ($result, MYSQLI_ASSOC);*/



 		$MotorOnTime = $timer['MotorOnTime'];

 		if($MotorOnTime == $now)
 		{
 			
           
  
            exec("".$timer['MotorOn']."");
            
	$motorId = $timer['motorId'];
	$updateMotor = "UPDATE motorName set motorStatus = 1 where motorId = '$motorId'";
	$resultMotor = mysqli_query($conn,$updateMotor);

 	$result = mysqli_query($conn,$userSql);


            $message = $timer['name']." Switched On" ;$title="Motor Alert";
           while($chkAllData = mysqli_fetch_assoc($result))
            {
		echo $chkAllData['deviceToken'];
                send_gcm_notify($chkAllData['deviceToken'],$message,$title,$dbhost);

            }




 		}
 	
}

$resultOff = mysqli_query($conn,$autoOff);
while($timerOff = mysqli_fetch_assoc($resultOff))
{
    
	$nowOff = date("H:i:00");
    

	
    
 	
        

 		$MotorOnTimeOff = $timerOff['MotorOffTime'];
 		if($MotorOnTimeOff == $nowOff)
 		{
 			
            $motorId = $timerOff['motorId'];
	$updateMotor = "UPDATE motorName set motorStatus = 0 where motorId = '$motorId'";
	$resultMotor = mysqli_query($conn,$updateMotor);


            
             exec("".$timerOff['MotorOff']."");

            $message = $timerOff['name']." Switched Off" ;$title="Motor Alert";
	    $resultOff = mysqli_query($conn,$userSql);
            while($user = mysqli_fetch_assoc($resultOff))
            {
	
                send_gcm_notify($user['deviceToken'],$message,$title,$dbhost);

            }



 			

 		}
 	
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
// echo "<br>";
//json_encode($fields);
//echo "<br>";
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
            if ($result === FALSE)
            {
                die('Problem occurred: ' . curl_error($ch));
            }
            curl_close($ch);

}