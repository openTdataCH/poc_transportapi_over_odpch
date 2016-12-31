<?php
/*
Copyright 2016 Matthias Günter, GnostX GmbH

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/
include_once "error.php";
include_once "curl.php";
include_once "location.php";
include_once "sec/keys.php";
include_once "sec/triprequest_template.php";

/**
* Gets a stationboard from the TRIAS interface of opentransportdata.swiss and returns a transport-API conform answer
* @param $id - the bpuic to use
* @param $limit - the number of public transport items
* $param $datetime - the time of interest
* $param $isdeparture - true = departure, false arrival
* @returns a transport-API conform answer with all information that is in the opentransport-system
*/
function getconnections($startbpuic,$stopbpuic,$starttime,$stoptime,$numres){

global $triprequestxml, $MYAPIKEY;

$xml=$triprequestxml;
$xml=str_replace("%%%StartBPUIC%%%",$startbpuic,$xml);
$xml=str_replace("%%%StopBPUIC%%%",$stopbpuic,$xml);

if ($numres >90 or $numres <1) {$numres=30;}
$xml=str_replace("%%%NumRES%%%",$numres,$xml);

$xml=replacetime($starttime,"%%%StartDateTime%%%",$xml);
$xml=replacetime($stoptime,"%%%StopDateTime%%%",$xml);

//var_dump_file("req1.log",$xml);
$url="https://api.opentransportdata.swiss/trias";

$res=do_curl_trias_api($url, $xml,$MYAPIKEY); 
file_put_contents("php://stdout", "\n$res");
// http://stackoverflow.com/questions/8830599/php-convert-xml-to-json
$xmldom = simplexml_load_string($res);
$json = json_encode($xmldom);
$array = json_decode($json,TRUE);

  //var_dump_file("res.log",$array);
  
  $work= $array['ServiceDelivery']['DeliveryPayload']['TripResponse']['TripResult'];
  $connections = array();
  $i=0;
  foreach ($work as $tripevent){
	    if (!isset ($tripevent['Trip'])){
			var_dump_file("log.txt",$tripevent);
			continue;
		}
		  //handle TripDuration
		  $duration=$tripevent['Trip']['Duration'];
		  
		  //handle Service
		  //not supported
		  
		  //handle products
		  $products = array();
		  $to=array();
		  $from=array();
		  //handle capacity
		  $capacity1st="-1"; //not supported
		  $capacity2nd="-1"; //not supported
		  
		  $leg=$tripevent['Trip']['TripLeg']['TimedLeg']; //only one for the time being!

		  //get from
		  $from['station']=$leg['LegBoard']['StopPointRef'];
		  //var_dump_file("leg.txt",$leg['LegBoard']['StopPointRef']);
		  //mylog($from['station']);
		  $from['name']=$leg['LegBoard']['StopPointName']['Text'];
		  $from['departure']=$leg['LegBoard']['ServiceDeparture']['TimetabledTime'];
		  $from['depprognostic']=$leg['LegBoard']['ServiceDeparture']['EstimatedTime']; //may not exist
		  $from['arrival']=null;
		  $from['arrivalprognostic']=null;
		  $from['capacity1st']="-1";
		  $from['capacity2nd']="-1";
		  
		  // doing to
		  $to['station']=$leg['LegAlight']['StopPointRef'];
		  $to['name']=$leg['LegAlight']['StopPointName']['Text'];
		  $to['departure']= null;
		  $to['depprognostic']= null;
		  $to['arrival']=$leg['LegAlight']['ServiceArrival']['TimetabledTime'];;
		  $to['arrivalprognostic']=$leg['LegAlight']['ServiceArrival']['EstimatedTime']; //may not exist
		  $to['capacity1st']="-1";
		  $to['capacity2nd']="-1";
	
		  //do service
		  $products[] =  $leg['Service']['Mode']['PtMode']; //@TODO Probably not compatible to transport-API
		  
		  
		  //build
		  $ttconnection = array();
		  $ttconnection['from'] = buildcheckpoint($from);
		  $ttconnection['to'] = buildcheckpoint($to);
		  $ttconnection['duration']=$duration;
		  $ttconnection['products']=$products;
		  $ttconnection['capacity1st']=$capacity1st;
		  $ttconnection['capacity2nd']=$capacity2nd;
		  array_push ($connections,$ttconnection);
	  

  }
	  $connboardwrap = array();
	  $connboardwrap['connections']=$connections;
	  return json_encode($connboardwrap);
}
		 

		 
/**
* Builds a transportAPI checkpoint element
* @info - contains the intermediate format to built it
* @returns a transport-API conform checkpoint
*/		 
function buildcheckpoint ($info){
	$chk = array();
	//mylog($info['station']);
	$chk['station']= getfirstlocationFull($info['station']); 
	$chk['arrival']=$info['arrival'];
	$chk['departure']=$info['departure'];
	$chk['platform']=$info['plannedBay'];
	$chk['prognosis']=buildprognostic($info['estimatedBay'],$info['depprognostic'],$info['arrprognostic']);
	
	return $chk;
	
}
		 
/**
* Builds a transportAPI prognosis element
* @platform - the estimated platform (if it exists)
* @departure - estimated departure (if it exsits)
* @arrival - estimated arrival (if it exists)
* @returns a transport-API conform prognosis element
*/		 
function buildprognostic($platform, $departure,$arrival){
	$prog = array();
	//mylog ($departure);
	$prog['platform'] = $platform;
	$prog['departure'] = $departure;
	$prog ['arrival'] = $arrival;
	$prog ['capacity1st'] = "-1"; //not supported
	$prog ['capacity2nd'] = "-1"; // not supported
	return $prog;
}
		 
/**
* Gets a stationboard from the TRIAS interface of opentransportdata.swiss and returns a transport-API conform answer
* @TODO the current system does not use the necessary priorisation of the station. It just takes the first one
* @param $station - the bpuic to use
* @param $limit - the number of public transport items
* $param $datetime - the time of interest
* $param $isdeparture - true = departure, false arrival
* @returns a transport-API conform answer with all information that is in the opentransport-system or false
*/
function getconnectionsbyname($start,$stop,$starttime,$stoptime,$numres){
	file_put_contents("php://stdout", "\n the station: >$station<");
	
	$startbpuic=getfirstlocation($start);
	$stopbpuic=getfirstlocation($stop);
	if (isset($startbpuic) and isset($stopbpuic)){
	  
      return getconnections($startbpuic,$stopbpuic,$starttime,$stoptime,$numres);
	  }
	$res = array();
	return $res; 
	
}


?>
