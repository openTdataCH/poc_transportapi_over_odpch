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

include_once "buffer.php";
include_once "error.php";
include_once "curl.php";


/**
* Builds a transportAPI JSON answer for locations
* @query - The query string
* @returns a transport-API conform location answer
*/	
function getlocationjson($query) {
	$res=getlocation($query);
	$r2value = array();
	$r2value['stations']=$res;
	return json_encode($r2value);	
}

/**
* Builds a transportAPI JSON answer for locations searched by coordinates
* @query - The query string
* @returns a transport-API conform location answer
*/	
function getlocationjsoncoord($x,$y) {
	$res=getlocationcoord($x,$y);
	$r2value = array();
	$r2value['stations']=$res;
	return json_encode($r2value);	
}

/**
* Builds a transportAPI answer for locations
* @query - the query string
* @returns rough result for locations
*/	
function getlocation($query){
	
	if (inbuffer('getlocation',$query)){
		return getbuffer('getlocation',$query);
	}
	$data = docurl("https://opentransportdata.swiss/api/action/package_show?id=bhlist");
	$myArr = $data->result->resources;
	
	$id=-1;
	if (strcmp($myArr[0]->{'identifier'},"Station list")==0){
		$id=$myArr[0]->{'id'};
	} else {
		$id=$myArr[1]->{'id'};
	}
	$geoid=-1;
	if (strcmp($myArr[0]->{'identifier'},"Station geographic")==0){
		$geoid=$myArr[0]->{'id'};
	} else {
		$geoid=$myArr[1]->{'id'};
	}	
	$qstr="https://opentransportdata.swiss/api/action/datastore_search?resource_id=%%%id%%%&q=%%%name%%%";
	$qstr=str_replace("%%%id%%%",$id,$qstr);
	$qstr=str_replace("%%%name%%%",$query,$qstr);
	
	$data = docurl($qstr);
	
	$rvalue= array();
	

	foreach ($data->result->records as $val){
		$station = array();
		$station['id'] = $val->StationID;
		$station['name']= $val->Station;
		$station['score'] = null;
		$station['coordinate'] = getcoordinates($val->StationID,$geoid);
		$station['distance'] = null;
		$rvalue[]=$station;
	}
	setbuffer('getlocation',$query,$rvalue); //buffering
	return $rvalue;
	
};	

/**
* Gets the coordinates in json format
* @param $bpuic - The bpuic code
* @param $id - the id of the ressource
* @returns the coordinates in json format
*/		
function getcoordinatesjson($bpuic,$id){
	$r2value['stations']=getcoordinates($bpuic,$id);
	return $r2value;	
}

/**
* Gets the coordinates in format
* @param $bpuic - The bpuic code
* @param $id - the id of the ressource
* @returns the coordinates
*/		
function getcoordinates($bpuic,$id){
	if (inbuffer('getcoordinates',$query)){
		return getbuffer('getcoordinates',$query);
	}

	$data = docurl("https://opentransportdata.swiss/api/action/package_show?id=bhlist");
	$myArr = $data->result->resources;
	$qstr="https://opentransportdata.swiss/api/action/datastore_search?resource_id=%%%id%%%&q=%%%name%%%";
	$qstr=str_replace("%%%id%%%",$id,$qstr);
	$qstr=str_replace("%%%name%%%",$bpuic,$qstr);
	$data = docurl($qstr);
	$data= $data->result->records;
	//var_dump($data);
    $x=$data[0]->Longitude;
    $y=$data[0]->Latitude;
	$station = array();
	$station['type'] = "WGS84";
	$station['x']= $x;
	$station['y']= $y;

	setbuffer('getlocation',$query,$station); //buffering
	return $station;		
}

/**
* Searches the locations by coordinates
* @param $x - The longitude
* @param $y - The latitude
* @returns an array of locations
*/		
function getlocationcoord($x,$y){
	
	if (inbuffer('getlocationcoord',$x."---".$y)){
		return getbuffer('getlocationcoord',$query);
	}
	$data = docurl("https://opentransportdata.swiss/api/action/package_show?id=bhlist");
	$myArr = $data->result->resources;
	
	$id=-1;
	if (strcmp($myArr[0]->{'identifier'},"Station list")==0){
		$id=$myArr[0]->{'id'};
	} else {
		$id=$myArr[1]->{'id'};
	}
	$geoid=-1;
	if (strcmp($myArr[0]->{'identifier'},"Station geographic")==0){
		$geoid=$myArr[0]->{'id'};
	} else {
		$geoid=$myArr[1]->{'id'};
	}	
	$qstr='SELECT * from "%%%id%%%" where (abs("Longitude" - %%%x%%%) <0.03) AND (abs("Latitude" - %%%y%%%) <0.03)';
	$qstr=str_replace("%%%id%%%",$geoid,$qstr);
	$qstr=str_replace("%%%x%%%",$x,$qstr);
	$qstr=str_replace("%%%y%%%",$y,$qstr);
	
    $qstr="https://opentransportdata.swiss/api/action/datastore_search_sql?sql=".urlencode($qstr);
	$data = docurl($qstr);
	
	$rvalue= array();
	//var_dump_file("query.res",$qstr);

	foreach ($data->result->records as $val){
		$station = array();
		$station['id'] = $val->StationID;
		$station['name']= $val->Remark; //@TODO we take it from the remark, which is not really well done
		$station['score'] = null;
		$station['coordinate'] = getcoordinates($val->StationID,$geoid); //@TODO his is also not very smooth
		$station['distance'] = haversineGreatCircleDistance($x,$y,$station['coordinate']['x'],$station['coordinate']['y']);
		$t=$station['id'];
		//file_put_contents("php://stdout", "\n>$t<");
		$rvalue[]=$station;
	}
	setbuffer('getlocationcoord',$x."---".$y,$rvalue); //buffering

	return $rvalue;
		
}

/**
* Gets the first location in the list. Be aware, that no special sorting is implemented
* @param $name - the name of the station
* @returns the first location or null
* @TODO better sorting
*/		
function getfirstlocationFull($name){
	$loc_struct=getlocation($name);
	$n=count($loc_struct);
	//file_put_contents("php://stdout", "\n num results>$n<");
	$log=json_encode($loc_struct);
	//file_put_contents("php://stdout", "\n>$log<");
	if (count($loc_struct)>0){
	  
	  
      return $loc_struct[0];
	  }
	return null;
}

/**
* Gets the first location
* @param $name - the name of the station
* @returns the first location or null
* @TODO better sorting
*/			
function getfirstlocation($name){
	$loc_struct=getlocation($name);
	$n=count($loc_struct);
	//file_put_contents("php://stdout", "\n num results>$n<");
	$log=json_encode($loc_struct);
	//file_put_contents("php://stdout", "\n>$log<");
	if (count($loc_struct)>0){
	  $id =$loc_struct[0]['id'];
	  file_put_contents("php://stdout", "\nid: >$id<");
      return $id;
	  }
	return null;
}



/**
 * Calculates the great-circle distance between two points, with
 * the Haversine formula.
 * @param float $latitudeFrom Latitude of start point in [deg decimal]
 * @param float $longitudeFrom Longitude of start point in [deg decimal]
 * @param float $latitudeTo Latitude of target point in [deg decimal]
 * @param float $longitudeTo Longitude of target point in [deg decimal]
 * @param float $earthRadius Mean earth radius in [m]
 * @return float Distance between points in [m] (same as earthRadius)
 * @see http://stackoverflow.com/questions/10053358/measuring-the-distance-between-two-coordinates-in-php
 */
function haversineGreatCircleDistance(
  $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
{
  // convert from degrees to radians
  $latFrom = deg2rad($latitudeFrom);
  $lonFrom = deg2rad($longitudeFrom);
  $latTo = deg2rad($latitudeTo);
  $lonTo = deg2rad($longitudeTo);

  $latDelta = $latTo - $latFrom;
  $lonDelta = $lonTo - $lonFrom;

  $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
    cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
  return $angle * $earthRadius;
}
?>
