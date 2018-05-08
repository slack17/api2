<?php
	date_default_timezone_set('Asia/Kuwait');
    $str = file_get_contents('/var/www/html/api/piPlant/data.json');
    $json = json_decode($str, true);
   echo 'ins';
    
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
	$allUser = "SELECT * from MotorTimer where cronStatus = 0 group by motorId";
	$userSql = "SELECT * from register where genNoti = 1";

	
	$result = mysqli_query($conn,$allUser);

	while($data = mysqli_fetch_assoc($result))
	{
	
	    $userId = $data['userId'];
	    $startTime = $data['startTime'];
	    $endTime = $data['endTime'];	
	    $cmd = $data['cmd'];
	    $motorId = $data['motorId'];
	 $endMin = $data['endMin'];
	 $endSec = $data['endSec'];
	    $time = date('Y-m-d h:i');
        $end_time = date('Y-m-d h:i', strtotime($endTime));


	    
if($endMin != 0)
{
$sleepTime = $endMin*60;
$sleepTime = $sleepTime+$endSec;
}
else
{
$sleepTime = $endSec;
}

sleep($sleepTime);
	$checkUser = "SELECT * from MotorTimer where cronStatus = 0 and motorId = '$motorId'";
	$rest = mysqli_query($conn,$checkUser);
	$res = mysqli_num_rows($rest);

	if($res > 0) {

			$updateMotor = "UPDATE motorName set motorStatus = 0 where motorId = '$motorId'";
			$resultMotor = mysqli_query($conn,$updateMotor);

			$updateMotor = "UPDATE MotorTimer set cronStatus = 1 where motorId = '$motorId'";
			$resultMotor = mysqli_query($conn,$updateMotor);
			
			$motorName = "SELECT * from motorName where motorId = '$motorId' ";
			$resultMotorName = mysqli_query($conn,$motorName);
			$dataName = mysqli_fetch_assoc($resultMotorName);
	
			$message = $dataName['name']." Switched Off";
			$title = "Motor Alarm";

			$resultOff = mysqli_query($conn,$userSql);
	
	
exec("sudo ".$cmd);
            while($user = mysqli_fetch_assoc($resultOff))
            {
		echo $user['userId'];
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

	            

	           echo	 $result = curl_exec($ch);

	            if ($result === FALSE)
	            {
	                die('Problem occurred: ' . curl_error($ch));
	            }
	            curl_close($ch);

	}


?>