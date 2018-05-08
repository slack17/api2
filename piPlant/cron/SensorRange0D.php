<?php

date_default_timezone_set('Asia/Kuwait');
$str = file_get_contents('/var/www/html/api/piPlant/data.json');
    $json = json_decode($str, true);
    #$obj = json_encode($json);
    //print_r($json);
    $host = $json['host'];
    $userName = $json['userName'];
    $password = $json['password'];
    $db = $json['db'];
    $soil = $json['soil'];
    $room = $json['room'];
    $water = $json['water'];
    





$servername = "localhost";
$username =  $userName;
$password = $password;
$dbname =  $db ;



   	$dbhost="localhost";
	$dbuser=$username;
	$dbpass=$password;
	$dbname=$dbname;

    $db = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    





// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) 
{
    die("Connection failed: " . $conn->connect_error);
} 
echo "script No bug";

//$sensorOn = "SELECT * from sensorAlert where status = 1 and min = 0";

$sensorOn = "SELECT * from sensorAlert JOIN sensorName on sensorName.device = sensorAlert.device  
where sensorAlert.status = 1 and sensorAlert.min = 0";

$userSql = "SELECT * from register";

$userSqlSoil = "SELECT * from register where genNoti = 1";
$userSqlRoom = "SELECT * from register where humNoti= 1";
$userSqlWater = "SELECT * from register where temNoti = 1";


$result = mysqli_query($conn,$sensorOn);
$data = $result->fetch_object();

if($data)
{

	$notify = 0;
	$qryStatus = $db->prepare($sensorOn);
	$qryStatus->execute();
	$js = $qryStatus->fetchAll(PDO::FETCH_OBJ);
	echo json_encode($js);echo "<br>";
 	foreach($js as $sensorAlert)
 	{
 		
 		echo "<br> Device:".$sensorAlert->device."<br>";


 		$lowRange = $sensorAlert->lowRange;
 		$highRange = $sensorAlert->highRange;
 		$type =$sensorAlert->type;
 		$device =$sensorAlert->device;
 		$name =$sensorAlert->name;

 		if($type == 1)#check Soil
 		{
			$sqlSoil = "SELECT *,sensorName.id as sensorId FROM ". $soil." 
	        JOIN sensorName on sensorName.device = ". $soil.".device
	        WHERE soil.device = '".$device."' and ". $soil." .id IN (SELECT MAX(id) FROM ". $soil." GROUP BY device)";

	        //echo "<br>".$sqlSoil."<br>";
         	$qrySoil = $db->prepare($sqlSoil);
			$qrySoil->execute();
		 	$chkDataSoil = $qrySoil->fetch(PDO::FETCH_OBJ);
		 	$moist = $chkDataSoil->moist_percentage;

		 	$senUpdate = "UPDATE sensorAlert set min = 1 where type = 1 and device = '$device'";
		 	echo $senUpdate;
		 	$qrySoilmin = $db->prepare($senUpdate);

		 	
		 	if($moist <= $lowRange)
		 	{
		 		
				$qrySoilmin->execute();

		 		$notify = 1;
		 		echo $device." low<br>";
		 		$message = $name." low";
		 	}
		 	else if($moist >= $highRange)
		 	{
		 		$qrySoilmin->execute();
		 		$notify = 1;
		 		echo $device." high<br>";
		 		$message = $name." high";

		 	}
		 	else
		 	{
		 		$notify = 0;
		 		echo $name." Normal<br>";
		 	}

	 		if($notify)
	 		{
	 			$qry = $db->prepare($userSqlSoil);
	            $qry->execute();
	            $chkAllData = $qry->fetchAll(PDO::FETCH_OBJ);

	            $title="Sensor Alert";
	            //continue;
	            foreach($chkAllData as $user)
	            {


	                send_gcm_notify($user->deviceToken,$message,$title,$dbhost);

	            }

	 		}
        
 		}
 		
 		else if($type != 1)#check Room 
 		{
 			
 			echo "no";
 			
		 	if($type == 2)#
		 	{
	 		$sqlCom = "SELECT * FROM ".$water." order by id desc limit 1";
 			$qry = $db->prepare($sqlCom);
			$qry->execute();
		 	$chkData = $qry->fetch(PDO::FETCH_OBJ);
		 	echo json_encode($chkData);

	 			$temperature = $chkData->temperature;

	 			$minUpdate = "UPDATE sensorAlert set min = 1  where type = 2 and device = '$device'";
		 		$qrymin = $db->prepare($minUpdate);

		 		if($temperature <= $lowRange)
			 	{
			 		$notify = 1;
			 		echo $name." low<br>";
			 		$message = $name." low";
			 		$qrymin->execute();
			 	}
			 	else if($temperature >= $highRange)
			 	{
			 		$notify = 1;
			 		echo $device." high<br>";
			 		$message =  $name." high";
			 		$qrymin->execute();

			 	}
			 	else
			 	{
			 		$notify = 0;
			 		echo $name." normal<br>";
			 		$qrymin->execute();
			 	}
			 	if($notify)
			 	{
			 		$qry = $db->prepare($userSqlWater);
		            $qry->execute();
		            $chkAllData = $qry->fetchAll(PDO::FETCH_OBJ);

		            $title="Sensor Alert";
		            foreach($chkAllData as $user)
		            {

		                send_gcm_notify($user->deviceToken,$message,$title,$dbhost);

		            }

			 	}

		 	}
		 	else
		 	{
	 		
	 		$sqlCom = "SELECT * FROM ".$room." order by id desc limit 1";
 			$qry = $db->prepare($sqlCom);
			$qry->execute();
		 	$chkData = $qry->fetch(PDO::FETCH_OBJ);
		 	echo json_encode($chkData);

		 		$Humidity = $chkData->Humidity;

		 		$minUpdate = "UPDATE sensorAlert set min = 1  where type = 3 and device = '$device'";
		 		$qrymin = $db->prepare($minUpdate);

		 		if($Humidity <= $lowRange)
			 	{
			 		$notify = 1;
			 		echo $device." low";
			 		$message = $name." low";
			 		$qrymin->execute();
			 	}
			 	else if($Humidity >= $highRange)
			 	{
			 		$notify = 1;
			 		echo $device." high";
			 		$message = $name." high";
			 		$qrymin->execute();

			 	}
			 	else
			 	{
			 		$notify = 0;
			 		echo $device." normal";
			 	}

			 	if($notify)
			 	{
			 		$qry = $db->prepare($userSqlRoom);
		            $qry->execute();
		            $chkAllData = $qry->fetchAll(PDO::FETCH_OBJ);

		            $title="Sensor Alert";
		            foreach($chkAllData as $user)
		            {

		                send_gcm_notify($user->deviceToken,$message,$title,$dbhost);

		            }

			 	}

		 	}


 		}
		 	
		 	
		echo "end"; 	


 		
 	}
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
            echo $result;
            if ($result === FALSE)
            {
                die('Problem occurred: ' . curl_error($ch));
            }
            curl_close($ch);

}