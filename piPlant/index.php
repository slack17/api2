<?php
date_default_timezone_set('Asia/Kuwait');
require_once('PHPMailer_5.2.0/class.phpmailer.php');
require 'Slim/Slim.php';

$app = new Slim();

$app->config('debug', true);
//$app->error('custom_error_handler');

//PHP 5 >= 5.3
$app->error(function ( Exception $e ) use ($app) 
{
    $app->render('error.php');
});

/*function custom_error_handler( Exception $e ){
$app = Slim::getInstance();
$app->render('error.php');
}*/


$app->POST('/register','register');
$app->POST('/getReading','getReading');
$app->POST('/updateMotorName','updateMotorName');
$app->POST('/updateMotorStatus','updateMotorStatus');
$app->POST('/setTimer','setTimer');
$app->POST('/updateSensor','updateSensor');
$app->POST('/setAlert','setAlert');
$app->get('/readJson','readJson');
$app->POST('/dbDetails','dbDetails');
$app->POST('/send','send');
$app->POST('/timerMotor','timerMotor');
$app->POST('/getSensorImage','getSensorImage');
$app->POST('/getTimerImage','getTimerImage');




//parking_lot_search($userId,$searchValue)



$app->run();



function getSensorImage()
{	
	$fileList = glob('images/sensor/*');
	 

	foreach($fileList as $filename)
	{
	   $data[] = $filename;
		 
	}
	$data1['imageUrls'] = $data;
	echo json_encode($data1);
}

function getTimerImage()
{	
	$fileList = glob('images/timer/*');
	 

	foreach($fileList as $filename)
	{
	   $data[] = $filename;
		 
	}
	$data1['imageUrls'] = $data;
	echo json_encode($data1);
}


function send()
{
$devicetoken="dYauN7xASfQ:APA91bFVJnfxO8kbTU6X7CnxeiZVz1EEOJaCRe85hQMcnnUWTHPa0G8e6A2XuGvYyG455xPskg2COq5QeJdBG1XwdGjmxL0TfoO0IM46cb2c3pykFbyDOd92v1sgZMawKyG0MLFcYt0l";$message="hi";$title="hi";$ip = 0;

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

function writeJson($data)
{
    
    $fp = fopen('
', 'w');
    fwrite($fp, $data);
    fclose($fp);

}


function readJson()
{
    $str = file_get_contents('data.json');
    $json = json_decode($str, true); 
    echo json_encode($json);
}



function registerOld()
{


    
    $request = Slim::getInstance()->request();#request Instance
    $req = json_decode($request->getBody());#get body json content
    $db = getConnection();#establish Db connection
    $date=date("Y-m-d H:i:s");
    $userId = $req->userId;
    $email = $req->email;
    $deviceToken = $req->deviceToken;
    $chk = "SELECT * from register where userId = $userId";
    $update = "UPDATE register set email = '$email',deviceToken = '$deviceToken' where userId = $userId";
    $insert = "INSERT INTO  register (userId,email,deviceToken) VALUES ($userId,'$email','$deviceToken')";

    $select  = "SELECT * from motorName where userId = $userId";
    $selectSensor  = "SELECT * from sensorName where userId = $userId";
    $uniqSen = "SELECT distinct device  FROM soil group by  device";


    $soilSen = $db->prepare($uniqSen);
    $soilSen->execute();
    $soilSenData = $soilSen->fetchAll(PDO::FETCH_OBJ);


    $qry = $db->prepare($chk);
    $qry->execute();
    $chkData = $qry->fetch(PDO::FETCH_OBJ);
    if($chkData)
    {
         $stmt = $db->prepare($update);
         $stmt->execute();
         #updated

        $qryMotor = $db->prepare($select);
        $qryMotor->execute();
        $qryMotorData = $qryMotor->fetchAll(PDO::FETCH_OBJ);

        $qrySen = $db->prepare($selectSensor);
        $qrySen->execute();
        $qrySenData = $qrySen->fetchAll(PDO::FETCH_OBJ);


        $res = array('Result'=>'Success',
                 'Status'=>'Registered successfully',
                 'userDetails'=>$qryMotorData,
                'soilSensor'=>$qrySenData
                );
        echo json_encode($res);exit;


    }
    else
    {
         $stmt = $db->prepare($insert);
         $stmt->execute();
         #inserted
         #motor Register 
         for($i = 0 ;$i< 5; $i++)
         {
            $insertButton= "INSERT INTO motorName (motorId, userId, name)VALUES ($i, '$userId', 'MOTOR $i');";
            $qryMotorIns = $db->prepare($insertButton);
            $qryMotorIns->execute();
         }
         foreach($soilSenData as $sen)
         {
            $insertSensor = "INSERT INTO sensorName (type, device, userId,name,status)VALUES (1, '$sen->device', '$userId','$sen->device',1)";
            $qrySenIns = $db->prepare($insertSensor);
            $qrySenIns->execute();

         }

        $qrySen = $db->prepare($selectSensor);
        $qrySen->execute();
        $qrySenData = $qrySen->fetchAll(PDO::FETCH_OBJ);

        $qryMotor = $db->prepare($select);
        $qryMotor->execute();
        $qryMotorData = $qryMotor->fetchAll(PDO::FETCH_OBJ);

        $res = array('Result'=>'Success',
                 'Status'=>'Registered successfully',
                 'motor'=>$qryMotorData,
                 'soilSensor'=>$qrySenData);
        echo json_encode($res);exit;

    }

}
function dbDetails()
{

	$request = Slim::getInstance()->request();#request Instance
    $req = json_decode($request->getBody());#get body json content
    $db = getConnection();#establish Db connection

    $date=date("Y-m-d H:i:s");
	$host = $req->host;
	$userName = $req->userName;
	$password = $req->password;
	$db = $req->db;
	$soil = $req->soil;
	$room = $req->room;
	$water = $req->water;

    /*$chk = "SELECT * from register where userId = $userId and email = $email";
    $qry = $db->prepare($chk);
    $qry->execute();
    $chkData = $qry->fetch(PDO::FETCH_OBJ);
    if($chkData)
    */
       

	$res = array('Result'=>'Success',
                 'host' =>$host,
				'userName' =>$userName,
				'password' =>$password,
				'db' =>$db,
				'soil' =>$soil,
				'room' =>$room,
				'water' =>$water
);
        $data = json_encode($res);
        writeJson($data);
        echo $data;exit;
        exit;

}
function register()
{
    

    $request = Slim::getInstance()->request();#request Instance
    $req = json_decode($request->getBody());#get body json content
    $db = getConnection();#establish Db connection
    $date=date("Y-m-d H:i:s");
    $userId = $req->userId;
    $email = $req->email;
    $deviceToken = $req->deviceToken;
    $sensorNames = $req->soilSensorName;
    $roomSensorName = $req->roomSensorName;
    $waterSensorName = $req->waterSensorName;
    $motorNames = $req->motorNames;

    $temNoti = $req->temNoti;
    $humNoti = $req->humNoti;
    $genNoti = $req->genNoti;

    $sensorList = explode(",",$sensorNames);
    $motorList = explode(",",$motorNames);
    $waterList = explode(",",$waterSensorName);
    $roomList = explode(",",$roomSensorName);
    


    $chk = "SELECT * from register where userId = $userId";
    $chkToken ="UPDATE register set deviceToken = '' where deviceToken = '$deviceToken'";
    $update = "UPDATE register set email = '$email',deviceToken = '$deviceToken' ,
    temNoti='$temNoti',humNoti='$humNoti',genNoti='$genNoti' where userId = $userId";
    $insert = "INSERT INTO  register (userId,email,deviceToken,temNoti,humNoti,genNoti) 
    VALUES ($userId,'$email','$deviceToken','temNoti','humNoti','genNoti')";

//SELECT * from motorName join timer on timer.motorId = motorName.motorId  where motorName.motorId IN('MOTOR1','MOTOR2', 'MOTOR3','MOTOR4')
    $select  = "SELECT motorName.*, timer.*, ifnull(MotorTimer.cronStatus,1) as countTimerStatus  from motorName join timer on timer.motorId = motorName.motorId LEFT JOIN MotorTimer on MotorTimer.motorId = motorName.motorId  where motorName.motorId IN($req->device) GROUP BY motorName.motorId";
    $selectSensor  = "SELECT * from sensorName join sensorAlert on sensorAlert.device = sensorName.device";
    $uniqSen = "SELECT distinct device  FROM soil group by  device";


    $soilSen = $db->prepare($uniqSen);
    $soilSen->execute();
    $soilSenData = $soilSen->fetchAll(PDO::FETCH_OBJ);


    $qry = $db->prepare($chk);
    $qry->execute();
    $chkData = $qry->fetch(PDO::FETCH_OBJ);
    if($chkData)
    {	
	$stmts = $db->prepare($chkToken);
         $stmts->execute();

         $stmt = $db->prepare($update);
         $stmt->execute();
         #updated

        $qryMotor = $db->prepare($select);
        $qryMotor->execute();
        $qryMotorData = $qryMotor->fetchAll(PDO::FETCH_OBJ);

        $qrySen = $db->prepare($selectSensor);
        $qrySen->execute();
        $qrySenData = $qrySen->fetchAll(PDO::FETCH_OBJ);
	
	    foreach($motorList as $motor)
         {
            $chkMo = "SELECT * from motorName where motorId = '$motor'";
            $qryMo = $db->prepare($chkMo);
            $qryMo->execute();
            $chkDataMo = $qryMo->fetch(PDO::FETCH_OBJ);
            if(!$chkDataMo)
            {
                $insertButton= "INSERT INTO motorName (motorId, userId, name,status)VALUES ('$motor', 0, '$motor',0);";
                $qryMotorIns = $db->prepare($insertButton);
                $qryMotorIns->execute();

                $insertButtonTimer= "INSERT INTO timer (motorId) VALUES ('$motor');";
                $qryMotorInsTime = $db->prepare($insertButtonTimer);
                $qryMotorInsTime->execute();
            }
         }
         foreach($sensorList as $sen)
         {
            $chkSen = "SELECT * from sensorName where device = '$sen'";
            $qrySen = $db->prepare($chkSen);
            $qrySen->execute();
            $chkDataSen = $qrySen->fetch(PDO::FETCH_OBJ);
            if(!$chkDataSen)
            {
                $insertSensor = "INSERT INTO sensorName (type, device, userId,name,status)VALUES (1, '$sen', '0','$sen',1)";
                $qrySenIns = $db->prepare($insertSensor);
                $qrySenIns->execute();

                $insertsensorAlert = "INSERT INTO sensorAlert (type, device)VALUES (1, '$sen')";
                $qrySenAlert = $db->prepare($insertsensorAlert);
                $qrySenAlert->execute();
            }   

         }
         foreach($waterList as $watersen)
         {

            $chkSen = "SELECT * from sensorName where device = '$watersen'";

            $qrySen = $db->prepare($chkSen);
            $qrySen->execute();
            $chkDataSen = $qrySen->fetch(PDO::FETCH_OBJ);
            if(!$chkDataSen)
            {
                $insertSensor = "INSERT INTO sensorName (type, device, userId,name,status)VALUES (2, '$watersen', '0','$watersen',1)";
                $qrySenIns = $db->prepare($insertSensor);
                $qrySenIns->execute();

                $insertsensorAlert = "INSERT INTO sensorAlert (type, device)VALUES (2, '$watersen')";
                $qrySenAlert = $db->prepare($insertsensorAlert);
                $qrySenAlert->execute();
            }   

         }
         foreach($roomList as $roomsen)
         {
            $chkSen = "SELECT * from sensorName where device = '$roomsen'";
            $qrySen = $db->prepare($chkSen);
            $qrySen->execute();
            $chkDataSen = $qrySen->fetch(PDO::FETCH_OBJ);
            if(!$chkDataSen)
            {
                $insertSensor = "INSERT INTO sensorName (type, device, userId,name,status)VALUES (3, '$roomsen', '0','$roomsen',1)";
                $qrySenIns = $db->prepare($insertSensor);
                $qrySenIns->execute();


                $insertsensorAlert = "INSERT INTO sensorAlert (type, device)VALUES (3, '$roomsen')";
                $qrySenAlert = $db->prepare($insertsensorAlert);
                $qrySenAlert->execute();
            }   

         }	

        $res = array('Result'=>'Success',
                 'Status'=>'Registered successfully',
                 'mototData'=>$qryMotorData,
                'soilSensor'=>$qrySenData
                );
        echo json_encode($res);exit;


    }
    else
    {
	$stmts = $db->prepare($chkToken);
         $stmts->execute();

         $stmt = $db->prepare($insert);
         $stmt->execute();
         #inserted
         #motor Register 
         foreach($motorList as $motor)
         {
            $chkMo = "SELECT * from motorName where motorId = '$motor'";
            $qryMo = $db->prepare($chkMo);
            $qryMo->execute();
            $chkDataMo = $qryMo->fetch(PDO::FETCH_OBJ);
            if(!$chkDataMo)
            {
                $insertButton= "INSERT INTO motorName (motorId, userId, name,status)VALUES ('$motor', 0, '$motor',0);";
                $qryMotorIns = $db->prepare($insertButton);
                $qryMotorIns->execute();

                $insertButtonTimer= "INSERT INTO timer (motorId) VALUES ('$motor');";
                $qryMotorInsTime = $db->prepare($insertButtonTimer);
                $qryMotorInsTime->execute();
            }
         }
         foreach($sensorList as $sen)
         {
            $chkSen = "SELECT * from sensorName where device = '$sen'";
            $qrySen = $db->prepare($chkSen);
            $qrySen->execute();
            $chkDataSen = $qrySen->fetch(PDO::FETCH_OBJ);
            if(!$chkDataSen)
            {
                $insertSensor = "INSERT INTO sensorName (type, device, userId,name,status)VALUES (1, '$sen', '0','$sen',1)";
                $qrySenIns = $db->prepare($insertSensor);
                $qrySenIns->execute();

                $insertsensorAlert = "INSERT INTO sensorAlert (type, device)VALUES (1, '$sen')";
                $qrySenAlert = $db->prepare($insertsensorAlert);
                $qrySenAlert->execute();
            }   

         }
         foreach($waterList as $watersen)
         {

            $chkSen = "SELECT * from sensorName where device = '$watersen'";

            $qrySen = $db->prepare($chkSen);
            $qrySen->execute();
            $chkDataSen = $qrySen->fetch(PDO::FETCH_OBJ);
            if(!$chkDataSen)
            {
                $insertSensor = "INSERT INTO sensorName (type, device, userId,name,status)VALUES (2, '$watersen', '0','$watersen',1)";
                $qrySenIns = $db->prepare($insertSensor);
                $qrySenIns->execute();

                $insertsensorAlert = "INSERT INTO sensorAlert (type, device)VALUES (2, '$watersen')";
                $qrySenAlert = $db->prepare($insertsensorAlert);
                $qrySenAlert->execute();
            }   

         }
         foreach($roomList as $roomsen)
         {
            $chkSen = "SELECT * from sensorName where device = '$roomsen'";
            $qrySen = $db->prepare($chkSen);
            $qrySen->execute();
            $chkDataSen = $qrySen->fetch(PDO::FETCH_OBJ);
            if(!$chkDataSen)
            {
                $insertSensor = "INSERT INTO sensorName (type, device, userId,name,status)VALUES (3, '$roomsen', '0','$roomsen',1)";
                $qrySenIns = $db->prepare($insertSensor);
                $qrySenIns->execute();


                $insertsensorAlert = "INSERT INTO sensorAlert (type, device)VALUES (3, '$roomsen')";
                $qrySenAlert = $db->prepare($insertsensorAlert);
                $qrySenAlert->execute();
            }   

         }

        $qrySen = $db->prepare($selectSensor);
        $qrySen->execute();
        $qrySenData = $qrySen->fetchAll(PDO::FETCH_OBJ);

        $qryMotor = $db->prepare($select);
        $qryMotor->execute();
        $qryMotorData = $qryMotor->fetchAll(PDO::FETCH_OBJ);


    $allUser = "SELECT * from register where userId != $userId";
    $qry = $db->prepare($allUser);
    $qry->execute();
    $chkAllData = $qry->fetchAll(PDO::FETCH_OBJ);

    $message ="Register Alert" ;$title="New User Registered";
    foreach($chkAllData as $user)
    {

        send_gcm_notify($user->deviceToken,$message,$title);

    }



       //writeJson($req);
        $res = array('Result'=>'Success',
                 'Status'=>'Registered successfully',
                 'mototData'=>$qryMotorData,
                 'soilSensor'=>$qrySenData);
        echo json_encode($res);exit;

    }

}

function getReading()
{
	
    $request = Slim::getInstance()->request();#request Instance
    $req = json_decode($request->getBody());#get body json content

    $db = getConnection();#establish Db connection

    $date=date("Y-m-d H:i:s");
    $userId = $req->userId;
    $email = $req->email;
    $deviceToken = $req->deviceToken;
    #$type= $req->type;
    $tblName = $req->tblName;
    $nData = $req->sensorType;
    $sensorType = $req->sensorType;
   
    if($sensorType="soil")
    {
        $sType = 1;
    }
    if($sensorType="water")
    {
        $sType = 2;
    }
    if($sensorType="room")
    {
        $sType = 3;
    }
    $sensorCount = $req->sensorCount;
    if($nData == "soil")
    {
        $sql = "SELECT *,sensorName.id as sensorId, sensorAlert.alertImage as alertImage FROM ".$tblName." 
        JOIN sensorName on sensorName.device = ".$tblName.".device
        join sensorAlert on sensorAlert.device = sensorName.device
        WHERE 
        ".$tblName.".id IN (SELECT MAX(id) FROM ".$tblName."  GROUP BY device)  and  ".$tblName.".device IN ($req->device) ";

        
        $qrytbl = $db->prepare($sql);
         $qrytbl->execute();
         $qrytblData = $qrytbl->fetchAll(PDO::FETCH_OBJ);
       
        $res = array('Result'=>'Success',
                 'Status'=>'',
                 'data'=>$qrytblData,
                 'sensorType'=>$nData,
                 'sensorCount'=>$sensorCount
                 );

    }
    else
    {
        $sql = "SELECT * FROM ".$tblName." order by id desc limit 1";
        $sensql = "SELECT * FROM sensorName
        join sensorAlert on sensorAlert.device = sensorName.device
        where sensorAlert.device = '$nData'";

        $qrytbl = $db->prepare($sql);
        $qrytbl->execute();
        $qrytblData = $qrytbl->fetchAll(PDO::FETCH_OBJ);

        $qrySentbl = $db->prepare($sensql);
        $qrySentbl->execute();
        $qrySentblData = $qrySentbl->fetch(PDO::FETCH_OBJ);

        $qrytblData[0]->sensorId = $qrySentblData->id;
        $qrytblData[0]->device = $qrySentblData->device;
        $qrytblData[0]->name = $qrySentblData->name;
        $qrytblData[0]->lowRange = $qrySentblData->lowRange;
        $qrytblData[0]->highRange = $qrySentblData->highRange;
	$qrytblData[0]->tempLowRange = $qrySentblData->tempLowRange;
 	$qrytblData[0]->tempHighRange = $qrySentblData->tempHighRange;
        $qrytblData[0]->status = $qrySentblData->status;
	$qrytblData[0]->alertImage = $qrySentblData->alertImage;
        

         $res = array('Result'=>'Success',
                 'Status'=>'',
                 'data'=>$qrytblData,
                 'sensorType'=>$nData,
                 'sensorCount'=>$sensorCount
                 );

    }
    
    

    
    echo json_encode($res);exit;

}

function timerMotor()
{
        $request = Slim::getInstance()->request();
        $req = json_decode($request->getBody());
	$db = getConnection();
        $stime = date('Y-m-d h:i:s');
        $hour = 0;
        $Minutes = $req->endHour;
	$seconds = $req->endMinutes;
	$userId = $req->userId;
        $motorId = $req->motorId;
        $cmd = $req->cmd;

$endTime = date('Y-m-d h:i:s',strtotime('+'.$hour.' hour +'.$Minutes.' minutes +' .$seconds.' seconds',strtotime($stime)));

    $select = "SELECT * from  MotorTimer where userId = '$userId' and motorId = '$motorId' ";

    $qry = $db->prepare($select);
    $qry->execute();
    $chkData = $qry->fetch(PDO::FETCH_OBJ);
    if($chkData)
    {
        

         $insert = "UPDATE  MotorTimer SET startTime = '$stime',endTime='$endTime',cronStatus = 0,cmd = '$cmd',endMin='$Minutes',endSec='$seconds' WHERE motorId = '$motorId' ";
        $qryinsert = $db->prepare($insert);
        $qryinsert->execute();
    }
    else
    {

 $insert = "INSERT INTO MotorTimer (userId,motorId, startTime,endTime,cmd,endMin,endSec) VALUES ('$userId','$motorId', '$stime','$endTime','$cmd','$Minutes','$seconds')";
        $qryinsert = $db->prepare($insert);
        $qryinsert->execute();
    }

        $res = array('Result'=>'Success',
                 'Status'=>'Registered successfully');

	echo json_encode($res);exit;
 }

function updateSensor()
{
    $request = Slim::getInstance()->request();#request Instance
    $req = json_decode($request->getBody());#get body json content
    $db = getConnection();#establish Db connection
    $date=date("Y-m-d H:i:s");
    $userId = $req->userId;
    $email = $req->email;
    $deviceToken = $req->deviceToken;
    $sensorId = $req->sensorId;
    $sensorName = $req->sensorName;

     $chk = "SELECT * from register where userId = $userId";
     $update = "UPDATE sensorName set name= '$sensorName' where id ='$sensorId'";
     $select = "SELECT *,id as sensorId from  sensorName ";

    $qry = $db->prepare($chk);
    $qry->execute();
    $chkData = $qry->fetch(PDO::FETCH_OBJ);
    if($chkData)
    {
        $qryUpdate = $db->prepare($update);
        $qryUpdate->execute();
       

        $qrySelect = $db->prepare($select);
        $qrySelect->execute();
        $qrySelectData = $qrySelect->fetchAll(PDO::FETCH_OBJ);
        $Result = "Success";

    }
    else
    {
        $Result = "Failed anauth";
        $qrySelectData=array();
    }

    $res = array(
        'Result'=>$Result,
         'data'=>$qrySelectData,
                 
                 );
        echo json_encode($res);exit;
}
function updateMotorStatus()
{
    $request = Slim::getInstance()->request();#request Instance
    $req = json_decode($request->getBody());#get body json content
    $db = getConnection();#establish Db connection
    $date=date("Y-m-d H:i:s");

    $userId = $req->userId;
    $motorId = $req->motorId;
    $status = $req->status;
    #
    
    $show = $status?"On":"Off";
    $trigger = $req->trigger;

    $chk = "SELECT * from register where userId = $userId";
    $update = "UPDATE timer set status = '$status' where motorId = '$motorId'";
	$updateMotor = "UPDATE motorName set motorStatus = '$status' where motorId = '$motorId'";
    $select = "SELECT * from motorName ";#where userId = '$userId'";
    $moterData = "SELECT * from motorName where motorId = '$motorId'";
	$updateMotorCron = "UPDATE MotorTimer set cronStatus = 1 where motorId = '$motorId'";
    $qrymoterData = $db->prepare($moterData);
    $qrymoterData->execute();
    $mdata = $qrymoterData->fetch(PDO::FETCH_OBJ);


    $qry = $db->prepare($chk);
    $qry->execute();
    $chkData = $qry->fetch(PDO::FETCH_OBJ);
    if($chkData)
    {
         
	 $qryUpM = $db->prepare($updateMotor);
         $qryUpM->execute();
         $qrySelect = $db->prepare($select);
         $qrySelect->execute();
 if($show == 'Off')
{
$qryUpMs = $db->prepare($updateMotorCron);
 $qryUpMs->execute();
}
   	 $message = $mdata->name." Switched ".$show ;$title="Motor Switched";
    exec("sudo ".$trigger);
	send_gcm_notify($chkData->deviceToken,$message,$title);
	

	  $update = "INSERT INTO updateMotorCron (userId,message,title,cronStatus) VALUES ('$userId','$message','$title','0')";
	$qryUp = $db->prepare($update);
	$qryUp->execute();
   	

         $state = "updated";
    } 
    else
    {
        $state = "please register";

    }    
     $res = array('Result'=>'Success',
                 'data'=>$state,
                 
                 );
    
    echo json_encode($res);exit;   

}

function updateMotorName()
{
    $request = Slim::getInstance()->request();#request Instance
    $req = json_decode($request->getBody());#get body json content
    $db = getConnection();#establish Db connection
    $date=date("Y-m-d H:i:s");

    $userId = $req->userId;
    $motorId = $req->motorId;
    //$status = $req->status;
    $name = $req->name;

    $chk = "SELECT * from register where userId = $userId";
    
    $update = "UPDATE motorName set name = '$name' where motorId = '$motorId'";
    $select = "SELECT * from motorName ";#where userId = $userId

    $moterData = "SELECT * from motorName where motorId = '$motorId'";

    $qrymoterData = $db->prepare($moterData);
    $qrymoterData->execute();
    $mdata = $qrymoterData->fetch(PDO::FETCH_OBJ);



    $qry = $db->prepare($chk);
    $qry->execute();
    $chkData = $qry->fetch(PDO::FETCH_OBJ);
    if($chkData)
    {
         $qryUp = $db->prepare($update);
         $qryUp->execute();

         $qrySelect = $db->prepare($select);
         $qrySelect->execute();

         $state = "updated";
    } 
    else
    {
        $state = "please register";

    } 

    $allUser = "SELECT * from register where userId != $userId";
    $qry = $db->prepare($allUser);
    $qry->execute();
    $chkAllData = $qry->fetchAll(PDO::FETCH_OBJ);
    $message = $mdata->name." Motor Named Updated" ;$title="Motor updated";

    foreach($chkAllData as $user)
    {

        send_gcm_notify($user->deviceToken,$message,$title);

    }

     $res = array('Result'=>'Success',
                 'data'=>$state,
                 
                 );
    echo json_encode($res);exit;   

}






function setTimer()
{
    
    $request = Slim::getInstance()->request();#request Instance
    $req = json_decode($request->getBody());#get body json content
    $db = getConnection();#establish Db connection
    $date=date("Y-m-d H:i:s");
	$userId = $req->userId;
    $motorId = $req->motorId;
    $deviceToken = $req->deviceToken;
    $email = $req->email;

   
    $MotorOn = $req->MotorOn;
    $MotorOff = $req->MotorOff;
    $MotorOnTime = $req->MotorOnTime;
    $MotorOffTime = $req->MotorOffTime;
    $fixedTime = $req->fixedTime;
    $name = $req->name;
    $status =  $req->status;
    $updateMotor = "UPDATE motorName set name ='$name' where motorId= '$motorId'";
    $updateMotorqry = $db->prepare($updateMotor);
    $updateMotorqry->execute();



$timerImage = $req->timerImage;




    $chk = "SELECT * from register where userId = $userId";
    $insert = "UPDATE  timer set userId = $userId,MotorOn = '$MotorOn',MotorOff ='$MotorOff' ,MotorOnTime='$MotorOnTime',MotorOffTime='$MotorOffTime',fixedTime= 'fixedTime',status = '$status' where motorId='$motorId'";
    

	$insert1 = "UPDATE motorName set timerImage = '$timerImage' where motorId='$motorId'";

    $lastTimer = "SELECT * from timer join motorName on motorName.motorId =timer.motorId  where timer.motorId= '$motorId'";
    
    $qry = $db->prepare($chk);
    $qry->execute();
    $chkData = $qry->fetch(PDO::FETCH_OBJ);
    if($chkData)
    {
        $qry = $db->prepare($insert);
        $qry->execute();


        $qry1 = $db->prepare($insert1);
        $qry1->execute();

        $qryGetLast = $db->prepare($lastTimer);
        $qryGetLast->execute();
        $qryGetLastData = $qryGetLast->fetch(PDO::FETCH_OBJ);


     $allUser = "SELECT * from register where userId = $userId";

     $qry = $db->prepare($allUser);
     $qry->execute();
    $chkAllData = $qry->fetch(PDO::FETCH_OBJ);
  
    $message =$qryGetLastData->name." timer updated" ;$title="Motor hasbeen updated";
    send_gcm_notify($chkAllData->deviceToken,$message,$title);

      $update = "INSERT INTO setTimerCron (userId,message,title,cronStatus) VALUES ('$userId','$message','$title','0')";
    $qryUp = $db->prepare($update);
    $qryUp->execute();
    
    

    
    



        $res = array('Result'=>'Success',
                 'data'=>$qryGetLastData,
                 );
    echo json_encode($res);exit;  

    }
    else
    {

    }


}



function setAlert()
{
	$request = Slim::getInstance()->request();#request Instance
    $req = json_decode($request->getBody());#get body json content
    $db = getConnection();#establish Db connection
    $date=date("Y-m-d H:i:s");

    $userId = $req->userId;
    //$motorId = $_POST['motorId'];
    $deviceToken = $req->deviceToken;
    $email = $req->email;

	$lowRange = $req->lowRange; 
	$highRange = $req->highRange;
	$status = $req->status;
	$device = $req->device;
	$name = $req->name;
	$tempLowRange = $req->tempLowRange;
	$tempHighRange = $req->tempHighRange;
	$ip = 0;

   	if(isset($_POST['ip']))
   	{
		$ip = $_POST['ip'];
   	}
    
    $updateMotor = "UPDATE sensorName set name ='$name' where device = '$device'";
    $updateMotorqry = $db->prepare($updateMotor);
    $updateMotorqry->execute();

       
	$alertImage = $req->alertImage;

    $chk = "SELECT * from register where userId = $userId";
  	if($req->tempLowRange != "" && $req->tempHighRange != "")
	{
		$update = "UPDATE sensorAlert set tempLowRange= '$tempLowRange',tempHighRange = '$tempHighRange',status='$status',
			lowRange='$lowRange',min=0,tempMin=0,highRange = '$highRange', alertImage = '$alertImage'
    		where device = '$device'";		

	}
	else
	{
		$update = "UPDATE sensorAlert set lowRange='$lowRange',min=0,highRange = '$highRange',alertImage = '$alertImage'
    	where device = '$device'";
	}
    

    $selectSensor  = "SELECT * from sensorName 
    join sensorAlert on sensorAlert.device = sensorName.device where sensorAlert.device = '$device'";
    
    $qry = $db->prepare($chk);
    $qry->execute();
    $chkData = $qry->fetch(PDO::FETCH_OBJ);
    if($chkData)
    {
        $qryUpdate = $db->prepare($update);
        $qryUpdate->execute();


        $qrySelect = $db->prepare($selectSensor);
        $qrySelect->execute();
        $qrySelectAll = $qrySelect->fetch(PDO::FETCH_OBJ);


		$allUser = "SELECT * from register where userId = $userId";
		$qry = $db->prepare($allUser);
		$qry->execute();
		$chkAllData = $qry->fetch(PDO::FETCH_OBJ);


    	$message = $qrySelectAll->name." Sensor updated" ;$title="Sensor hasbeen updated";
		send_gcm_notify($chkAllData->deviceToken,$message,$title,$ip);

      	$update = "INSERT INTO setAlertCron (userId,message,title,cronStatus) VALUES ('$userId','$message','$title','0')";
	    $qryUp = $db->prepare($update);
	    $qryUp->execute();
    

        $res = array('Result'=>'Success',
                 'data'=>$qrySelectAll,
                 );
        echo json_encode($res);exit;
        
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




function forgotpassword(){


    $request = Slim::getInstance()->request();
    $register = json_decode($request->getBody());
    $date=date('Y-m-d H:i:s');
    $sql = "select firstName,lastName,tempToken from ah_customer where email=:email";

    try{
        $db = getConnection();

        $stmt = $db->prepare($sql);
        $stmt->bindParam("email", $register->email);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_OBJ);
        if($customer)
        {
            $subject="Forgot Password";
            $username = $customer->firstName;
            $token = $customer->tempToken;
            $id = urlencode(base64_encode($register->email));
            $content = "So you lost your password? No problem! Please click the link below to reset your password<br><br>
            <a href='52.35.102.74/agilehealth/reset.php/?id=$id'>Click here</a>";
            $sign = "Thank you<br>
            AgileHealth<br><br>
            © 2017AgileHealth. All right reserved.";
            $html='<html class="no-js" lang="en">
            <body>
            <div style="
            width: auto;
            border: 15px solid #efc01a;
            padding: 20px;
            margin: 10px;
            ">
            <div class="container">
            <div class="navbar-header">
            <div style="text-align: center;">
            <a href="" title="" style="margin-top:0px"><img src="http://52.35.102.74/logo-new.png"  class="img-responsive logo-new" ></a>
            </div>

            <span style="float:right; text-align:right;">

            </span>

            <div style="clear:both;" ></div>
            <hr width="100%" />
            </div>
            <div class="mail-container">
            <br />
            <b> '.$username.' </b>
            <br />
            <br />
            '.$content.'
            <br />
            </div>
            <br />
            <hr width="100%" />
            <footer class="navbar-inverse">
            <div class="row">
            '.$sign.'
            <div class="collapse navbar-collapse"></div>
            </div>
            </footer>
            </div>
            </body>
            </html>';
            $mail       = new PHPMailer();
$mail->IsSMTP(); // enable SMTP
$mail->SMTPAuth = true; // authentication enabled
$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
$mail->Host = "smtp.gmail.com";
$mail->Port = 465; // or 587
$mail->IsHTML(true);
$mail->Username = 'codekhadimail@gmail.com';//codekhadimail@gmail.com
$mail->Password = '!@#qweasd';//'!@#qweasd';//
$mail->From = $mail->Username; //Default From email same as smtp user
$mail->FromName = "agilehealth";
$mail->AddAddress($register->email, '');
$mail->CharSet = 'UTF-8';
$mail->Subject    = $subject;
$mail->MsgHTML($html);
if($mail->Send())
{
    $res = array('Result'=>'Success',
                 'Status'=>'Password reset link has been sent to your mail');
    echo json_encode($res); exit;
//echo '{ "Result": "Success","Status":"Password reset link has been sent to your mail"}';
} else {
    $res = array('Result'=>'Failed',
                 'Status'=>'Email sent failed');
    echo json_encode($res); exit;

    echo '{"Result":"Failed","Status":"Email sent failed"}';
}
}

else
{
    $res = array('Result'=>'Failed',
                 'Status'=>'User not found');
    echo json_encode($res); exit;
}
}
catch(PDOException $e)
{

    $res = array('Result'=>'Failed','error'=>$e);
    echo json_encode($res); exit;
}
}

function verify($id,$token)
{
    $date=date('Y-m-d H:i:s');
    $email = base64_decode($id);
//echo $email;
//echo $token;
    echo "<br>";
    $qry="update ah_customer set profileStatus=1,updated_at='$date' where email=:email and tempToken=:tempToken";
    try
    {
        $db = getConnection();
        $stmt = $db->prepare($qry);
        $stmt->bindParam("email", $email);
        $stmt->bindParam("tempToken", $token);
        $stmt->execute();
//$user = $stmt->fetch(PDO::FETCH_OBJ);
        $html = "<center><h2>Your account has been verified successfully.</h2></center>";
        echo $html;
    }
//header('Location : verify.php');
    catch(PDOException $e)
    {
        echo $e;
        $res = array('Result'=>'Failed');
        echo json_encode($res); exit;
    }

}

function getConnection() {

    $dbhost="localhost";
    $dbuser="khaled";
    $dbpass="test123";
    $dbname="phpauto";
 $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   return $dbh;


}


