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
include_once "sec/stopevent_template.php";

/**
* Gets a stationboard from the TRIAS interface of opentransportdata.swiss and returns a transport-API conform answer
* @param $id - the bpuic to use
* @param $limit - the number of public transport items
* $param $datetime - the time of interest
* $param $isdeparture - true = departure, false arrival
* @returns a transport-API conform answer with all information that is in the opentransport-system
*/
function getstationboard($id,$limit,$datetime,$isdeparture){
global $stopeventxml, $MYAPIKEY;

$xml=$stopeventxml;
$xml=str_replace("%%%BPUIC%%%",$id,$xml);

if ($limit >90 or $limit <1) {$limit=30;}
$xml=str_replace("%%%LIMIT%%%",$limit,$xml);

if (isset($datetime)){
	if (validateDate($datetime)){
		$mytime="<DepArrTime>" . getTheDate($datetime)->format("Y-m-d\TH:i:s") . "</DepArrTime>";
		$xml=str_replace("%%%TIME%%%",$mytime,$xml);
	} else {
		$xml=str_replace("%%%TIME%%%","",$xml);
	}
	
} else {
			$xml=str_replace("%%%TIME%%%","",$xml);
	}
	
if ($isdeparture) {
	$xml=str_replace("%%%TYPE%%%","departure",$xml);	
} else {
	$xml=str_replace("%%%TYPE%%%","arrival",$xml);	
}
$xml=str_replace("%%%bPC%%%","false",$xml);	 //we don't need previous Calls
$xml=str_replace("%%%bOC%%%","false",$xml);	//we don't need onwardCalls
$xml=str_replace("%%%bRT%%%","true",$xml);	

$url="https://api.opentransportdata.swiss/trias";

$res=do_curl_trias_api($url, $xml,$MYAPIKEY); 

// http://stackoverflow.com/questions/8830599/php-convert-xml-to-json
$xmldom = simplexml_load_string($res);
$json = json_encode($xmldom);
$array = json_decode($json,TRUE);

  //var_dump_file($array);
  
  $work= $array['ServiceDelivery']['DeliveryPayload']['StopEventResponse']['StopEventResult'];
  $stationboard = array();
  $i=0;
  foreach ($work as $stopevent){
	    if (!isset ($stopevent['StopEvent'])){
			var_dump_file("log.txt",$stopevent);
			continue;
		}
	   //skip the first element
	   //var_dump($stopevent);
        //echo "run: ". $i . "\n\n\n";
		$i=$i+1;
		  //handle ThisCall
		  if (! isset($stopevent['StopEvent']['ThisCall'])) {
			  var_dump_file($stopevent);
			   exit;
		  }
		  $thiscall=$stopevent['StopEvent']['ThisCall'];
		  $bpuic = $thiscall['CallAtStop']['StopPointRef'];
		  $arr_tt= null;
		  $arr_es= null;
		  if (isset($thiscall['CallAtStop']['ServiceArrival'])){
		    $arr_tt =$thiscall['CallAtStop']['ServiceArrival']['TimetabledTime'];
			if (isset ($thiscall['CallAtStop']['ServiceArrival']['EstimatedTime'])){
		      $arr_es =$thiscall['CallAtStop']['ServiceArrival']['EstimatedTime'];
			  
			}
            }
		  $dep_tt= null;
		  $dep_es= null;
		  if (isset($thiscall['CallAtStop']['ServiceDeparture'])){
		    $dep_tt =$thiscall['CallAtStop']['ServiceDeparture']['TimetabledTime'];
			if (isset($thiscall['CallAtStop']['ServiceDeparture']['EstimatedTime'])) {
				
		      $dep_es =$thiscall['CallAtStop']['ServiceDeparture']['EstimatedTime'];
		      }
            }
		  //@TODO  we have todo the bays
		  $plat_tt = '';
		  $loc_arr = array();
		  $plat_es = '';
		  $loc_arr=getlocation($bpuic);
		  //handle Service
		  $name = $stopevent['StopEvent']['Service']['JourneyRef'];
		  
		  $category = $stopevent['StopEvent']['Service']['Mode']['Name']['Text'];
		  $operator = $stopevent['StopEvent']['Service']['OperatorRef'];
		  $number =  $stopevent['StopEvent']['Service']['PublishedLineName']['Text'];
		  //var_dump_file($stopevent['StopEvent']['Service']);
		  $to = $stopevent['StopEvent']['Service']['DestinationText']['Text'];
		  
		  //all the rest is discarded
		  
		  //build
		  $stop = array();
		  $stop['station']=$loc_arr;
		  $stop['arrival']=$arr_tt;  //@TODO Handle Zulutime
		  $stop['arrivalTimeStamp']=null; 
		  $stop['departure']=$dep_tt; //@TODO Handle Zulutime
		  $stop['departureTimestamp']=null;
		  $stop['platform']=$plat_tt;
		  $prog = array();
		  $prog['platform']=$plat_es;
		  $prog['arrival']= $arr_es;
		  $prog['departure']= $dep_es;
		  $prog['capacity1st']= "-1";
		  $prog['capacity2nd']= "-1";
		  $stop['prognosis']=$prog;
		  $stop['name']=$name;
		  $stop['category']=$category;
		  $stop['number']=$number;
		  $stop['operator']=$operator;
		  $stop['to']=$to;
		  $stopwarp = array();
		  $stopwrap['stop']=$stop;
		  array_push ($stationboard,$stopwrap);
	  

  }
	  $stationboardwrap = array();
	  $stationboardwrap['stationboard']=$stationboard;
	  return json_encode($stationboardwrap);
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
function getstationboardbyname($station,$limit,$datetime,$isdeparture){
	//file_put_contents("php://stdout", "\n the station: >$station<");
	$loc_struct=getlocation($station);
	$n=count($loc_struct);
	//file_put_contents("php://stdout", "\n num results>$n<");
	$log=json_encode($loc_struct);
	//file_put_contents("php://stdout", "\n>$log<");
	if (count($loc_struct)>0){
	  $id =$loc_struct[0]['id'];
	  //file_put_contents("php://stdout", "\nid: >$id<");
      return getstationboard($id,$limit,$datetime,$isdeparture);
	  }
	$res = array();
	return $res; 
	
}


?>
