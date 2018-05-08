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
    







   	$dbhost="localhost";
	$dbuser=$userName;
	$dbpass=$password;
	$dbname=$db;

    
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);





$sensorOn = "SELECT * from sensorAlert JOIN sensorName on sensorName.device = sensorAlert.device 
 where sensorAlert.status = 1";
$userSql = "SELECT * from register";

$userSqlSoil = "SELECT * from register where genNoti = 1";
$userSqlRoom = "SELECT * from register where humNoti= 1";
$userSqlWater = "SELECT * from register where temNoti = 1";


$result = mysqli_query($conn,$sensorOn);


while($sensorAlert = mysqli_fetch_assoc($result))
{

	
 	
 		

          
 		$lowRange = $sensorAlert['lowRange'];
 		$highRange = $sensorAlert['highRange'];
	    	$templowRange = $sensorAlert['tempLowRange'];
 		$temphighRange = $sensorAlert['tempHighRange'];

 		$type =$sensorAlert['type'];
 		 $device =$sensorAlert['device'];
 		 $name =$sensorAlert['name'];
 		 $min =$sensorAlert['min'];
 		 $minTemp =$sensorAlert['tempMin'];

 		if($type == 1)
 		{
			$sqlSoil = "SELECT *,sensorName.id as sensorId FROM ". $soil." 
	        JOIN sensorName on sensorName.device = ". $soil.".device
	        WHERE soil.device = '".$device."' and ". $soil." .id IN (SELECT MAX(id) FROM ". $soil." GROUP BY device)";

	       
         	$qrySoil = mysqli_query($conn,$sqlSoil);
		 	$chkDataSoil = mysqli_fetch_assoc($qrySoil);
		 	$moist = $chkDataSoil['moist_percentage'];

		 	$senUpdate = "UPDATE sensorAlert set min = 1 where type = 1 and device = '$device'";
		 	$senUpdateZero = "UPDATE sensorAlert set min = 0 where type = 1 and device = '$device'";
		 
		 	

		 	
		 	if($moist <= $lowRange)
		 	{
		 		
			
				if($min == 0)
				{
					$notify = 1;
		 		$qrySoilmin = mysqli_query($conn,$senUpdate);
		 		$message = $name." Moisture is ".$moist;
				}
				else
				{
					$notify = 0;
				}
		 		
		 	}
		 	else if($moist >= $highRange)
		 	{
		 		
		 				if($min == 0)
		 				{
		 		$qrySoilmin = mysqli_query($conn,$senUpdate);
		 		$message = $name."Moisture is ".$moist;
		 				}
		 				else
		 				{
		 					$notify = 0;
		 				}
		 		
		 		

		 	}
		 	else if($moist >= $lowRange && $moist <= $highRange)
		 	{
		 		$qrySoilmin = mysqli_query($conn,$senUpdateZero);
		 		$notify = 0;
		 	}
		 	else
		 	{
		 		$notify = 0;
		 		
		 	}
	
	 		if($notify==1)
	 		{
	 		

		
	
	 			$qry =	mysqli_query($conn,$userSqlSoil);
	           
	         

	            $title="Sensor Alert";
	            
	             while($chkAllData = mysqli_fetch_assoc($qry))
	            {


	                send_gcm_notify($chkAllData['deviceToken'],$message,$title,$dbhost);

	            }

	 		}
        
 		}
 		
 		else if($type != 1)
 		{
 			
 			
 			
		 	if($type == 2)
		 	{
	 		$sqlCom = "SELECT * FROM ".$water." order by id desc limit 1";
 			$qry = mysqli_query($conn,$sqlCom);
		 	$chkData = mysqli_fetch_assoc($qry);
		 	

	 			$temperature = $chkData['temperature'];

	 			$minUpdate = "UPDATE sensorAlert set min = 1  where type = 2 and device = '$device'";
	 			$minUpdateZero = "UPDATE sensorAlert set min = 0  where type = 2 and device = '$device'";
		 		

		 		if($temperature <= $lowRange)
			 	{
			 		if($min == 0)
			 		{
			 		$notify = 1;
			 		$message = $name." Temperature is ".$temperature;	
			 		$qrymin = mysqli_query($conn,$minUpdate);
			 		}
			 		else
			 		{
			 			$notify = 0;
			 		}
			 		
			 		
			 		
			 	}
			 	else if($temperature >= $highRange)
			 	{
			 		if($min == 0)
			 		{
			 		$notify = 1;
			 		$message = $name." Temperature is ".$temperature;
			 		$qrymin = mysqli_query($conn,$minUpdate);
			 		}
			 		else
			 		{
			 			$notify = 0;
			 		}
			 		
			 		

			 	}
			 	else if($temperature >= $lowRange && $temperature <= $highRange)
			 	{


			 		$qrymin = mysqli_query($conn,$minUpdateZero);
			 		$notify = 0;
			 	}
			 	else
			 	{
			 		$notify = 0;
			 		
			 		
			 	}


			 	if($notify==1)
			 	{	
			 		
			 		$qry =	mysqli_query($conn,$userSqlWater);

		            $title="Sensor Alert";
		            while($chkAllData = mysqli_fetch_assoc($qry))
		            {


		                send_gcm_notify($chkAllData['deviceToken'],$message,$title,$dbhost);

		            }

			 	}

		 	}
		 	else
		 	{
	 		
	 		$sqlCom = "SELECT * FROM ".$room." order by id desc limit 1";

	 		$qry = mysqli_query($conn,$sqlCom);
		 	$chkData = mysqli_fetch_assoc($qry);

 		

		 		$Humidity = $chkData['Humidity'];
				$temperature = $chkData['temperature']; 
		 		$minUpdate = "UPDATE sensorAlert set min = 1  where type = 3 and device = '$device'";
		 		$minUpdateZero = "UPDATE sensorAlert set min = 0  where type = 3 and device = '$device'";

		 		$minTempUpdate = "UPDATE sensorAlert set tempMin = 1  where type = 3 and device = '$device'";
		 		$minTempUpdateZero = "UPDATE sensorAlert set tempMin = 0  where type = 3 and device = '$device'";

		 		// $qrymin = mysqli_query($conn,$minUpdate); 
			


		 		if($Humidity <= $lowRange)
			 	{
			 		if($min == 0)
			 		{
			 		$notify = 1;
			 		$messageHum = $name." Humidity is ".$Humidity;
			 		
			 		$qrymin = mysqli_query($conn,$minUpdate);
			 		}
			 		else
			 		{
			 			$notify = 0;
			 		}
			 		
			 	}
			 	else if($Humidity >= $highRange)
			 	{
			 		if($min == 0)
			 		{
			 		$notify = 1;
			 		$messageHum = $name." Humidity is ".$Humidity;
			 		
			 			$qrymin = mysqli_query($conn,$minUpdate);	
			 		}
			 		else
			 		{
			 			$notify = 0;
			 		}
			 		

			 	}
			 	else if($Humidity >= $lowRange && $Humidity <= $highRange)
			 	{
			 		$notify = 0;
			 		$qrymin = mysqli_query($conn,$minUpdateZero);	
			 	}
			 	else
			 	{
			 		$notify = 0;
			 		
			 	}

			
		 		if($temperature <= $templowRange)
			 	{
			 		if($minTemp == 0)
			 		{
			 		$notifyTemp = 1;
			 		$message = $name." Temperature is ".$temperature;
			 		
			 		$qrymin = mysqli_query($conn,$minTempUpdate);	
			 		}
			 		else
			 		{
			 			$notifyTemp = 0;
			 		}
			 		
			 	}
			 	else if($temperature >= $temphighRange)
			 	{
			 		if($minTemp == 0)
			 		{
			 		$notifyTemp = 1;
			 		$message = $name." Temperature is ".$temperature;
			 		
			 		$qrymin = mysqli_query($conn,$minTempUpdate);	
			 		}
			 		else
			 		{
			 		$notifyTemp = 0;	
			 		}
			 

			 	}
			 	else if($templowRange >= $temperature && $temphighRange <= $temperature)
			 	{
			 		$notifyTemp = 0;
			 		$qrymin = mysqli_query($conn,$minTempUpdateZero);	
			 	}
			 	else
			 	{
			 		$notifyTemp = 0;
			 		
			 	} 


			echo 'notify'.$notify.'tempNoti'.$notifyTemp;

			 	if($notify==1)
			 	{
			 		
		        $qry =	mysqli_query($conn,$userSqlRoom);
		            $title="Sensor Alert";
		            while($chkAllData = mysqli_fetch_assoc($qry))
		            {
		
		                send_gcm_notify($chkAllData['deviceToken'],$messageHum,$title,$dbhost);

		            }

			 	}

			if($notifyTemp==1)
			 	{
			 		
		        $qry =	mysqli_query($conn,$userSqlWater);
		            $title="Sensor Alert";
		            while($chkAllData = mysqli_fetch_assoc($qry))
		            {


		                send_gcm_notify($chkAllData['deviceToken'],$message,$title,$dbhost);

		            }

			 	}

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

            

            $result = curl_exec($ch);
           
            if ($result === FALSE)
            {
                die('Problem occurred: ' . curl_error($ch));
            }
            curl_close($ch);

}