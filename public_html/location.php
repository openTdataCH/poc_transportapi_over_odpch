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

function checkDidokResult($didok) {
	if ($didok->success === false) {
		trigger_error("didok curl not successfull. Check the ckanApiUrl.", E_USER_ERROR);
        return false;
	}
	if (!isset($didok->result)) {
		trigger_error("didok curl returned no result. Check the ckanApiUrl.", E_USER_ERROR);
        return false;
	}
	if (!isset($didok->result->resources)) {
		trigger_error("didok curl returned no resources. Check the ckanApiUrl.", E_USER_ERROR);
        return false;
	}
	if (!isset($didok->result->resources[3])) {
		trigger_error("didok curl returned no dienststellen_full. Check the ckanApiUrl.", E_USER_ERROR);
        return false;
	}
	return true;
}

function checkDatastoreSearchResult($data) {
	if ($data->success === false) {
		trigger_error("didok search curl not successfull. Check the ckanApiUrl.", E_USER_ERROR);
        return false;
	}
	if (!isset($data->result)) {
		trigger_error("didok search curl returned no result. Check the ckanApiUrl.", E_USER_ERROR);
        return false;
	}
	if (!isset($data->result->records)) {
		trigger_error("didok search curl returned no result records. Check the ckanApiUrl.", E_USER_ERROR);
        return false;
	}
	return true;
}

/**
* Builds a transportAPI JSON answer for locations
* @query - The query string
* @returns a transport-API conform location answer
*/	
function getLocationJson($ckanApiKey, $ckanApiUrl, $query) {
	$result = getLocation($ckanApiKey, $ckanApiUrl, $query);
	$r2value = array();
	$r2value['stations'] = $result;
	return json_encode($r2value);	
}

/**
* Builds a transportAPI JSON answer for locations searched by coordinates
* @query - The query string
* @returns a transport-API conform location answer
*/	
function getLocationJsonCoordinates($ckanApiKey, $ckanApiUrl, $x, $y) {
	$res = getLocationCoordinates($ckanApiKey, $ckanApiUrl, $x, $y);
	$r2value = array();
	$r2value['stations'] = $res;
	return json_encode($r2value);	
}

/**
* Builds a transportAPI answer for locations
* @query - the query string
* @returns rough result for locations
*/	
function getLocation($ckanApiKey, $ckanApiUrl, $station) {
	if (inbuffer('getLocation', $station)) {
		return getbuffer('getLocation', $station);
	}

	$didok = doCurl($ckanApiUrl . "/package_show?id=didok", $ckanApiKey);
	if (!checkDidokResult($didok)) {
		return [];
	}

	$queryId = $didok->result->resources[3]->id;
	$station = urlencode($station);
	$query = $ckanApiUrl . "/datastore_search?resource_id=" . $queryId . '&q=' . $station;

	$data = doCurl($query, $ckanApiKey);
	if (!checkDatastoreSearchResult($data)) {
		return [];
	}

	$resultRecords = $data->result->records;
	$locationReducedArray = [];
	foreach ($resultRecords as $recordKey => $record) {
		$station = array();
		$station['id'] = $record->BPUIC;
		$station['sloid'] = $record->SLOID;
		$station['name'] = $record->BEZEICHNUNG_OFFIZIELL;
		$iers = null;
		if ($record->Z_WGS84) {
			$iers = $record->Z_WGS84;
		}
		$station['coordinate'] = [
			"type" 		=> "WGS84",
            "latitude" 	=> $record->N_WGS84,
			"longitude" => $record->E_WGS84,
			"IERS" 		=> $iers,
		];
		$locationReducedArray[$recordKey] = $station;
	}

	setbuffer('getLocation', $query, $locationReducedArray); // buffering
	return $locationReducedArray;
};	

/**
* Gets the coordinates in json format
* @param $bpuic - The bpuic code
* @param $id - the id of the ressource
* @returns the coordinates in json format
*/
// INFO: this seems not to be used
function getCoordinatesJson($bpuic,$id) {
	$r2value['stations'] = getCoordinates($bpuic, $id);
	return $r2value;	
}

/**
* Gets the coordinates in format
* @param $bpuic - The bpuic code
* @param $id - the id of the ressource
* @returns the coordinates
*/
// INFO: this seems not to be used. If it should be used, it should be adjusted to the new API
function getCoordinates($bpuic, $id) {
	if (inbuffer('getCoordinates', $query)) {
		return getbuffer('getCoordinates', $query);
	}

	$data = doCurl("https://opentransportdata.swiss/api/action/package_show?id=bhlist");
	$myArr = $data->result->resources;
	$qstr = "https://opentransportdata.swiss/api/action/datastore_search?resource_id=%%%id%%%&q=%%%name%%%";
	$qstr = str_replace("%%%id%%%", $id, $qstr);
	$qstr = str_replace("%%%name%%%", $bpuic, $qstr);
	$data = doCurl($qstr);
	$data = $data->result->records;
    $x = $data[0]->Longitude;
    $y = $data[0]->Latitude;
	$station = array();
	$station['type'] = "WGS84";
	$station['x'] = $x;
	$station['y'] = $y;

	setbuffer('getLocation', $query, $station); // buffering
	return $station;		
}

/**
* Searches the locations by coordinates
* @param $x - The longitude
* @param $y - The latitude
* @returns an array of locations
*/		
function getLocationCoordinates($ckanApiKey, $ckanApiUrl, $x, $y) {
	if (inbuffer('getLocationCoordinates', $x . "---" . $y)) {
		return getbuffer('getLocationCoordinates', $query);
	}

	$didok = doCurl($ckanApiUrl . "/package_show?id=didok", $ckanApiKey);
	if (!checkDidokResult($didok)) {
		return [];
	}

	$queryId = $didok->result->resources[3]->id;

	$lat = abs($x);
	$latPlus = $lat + 0.03;
	$latMinus = $lat - 0.03;
	$long = abs($y);
	$longPlus = $long + 0.03;
	$longMinus = $long - 0.03;
	$query = urlencode('SELECT * from "' . $queryId . '" WHERE "E_WGS84" < ' . $latPlus . ' AND "E_WGS84" > ' . $latMinus . ' AND "N_WGS84" < ' . $longPlus . ' AND "N_WGS84" > ' . $longMinus);
	$urlQuery = $ckanApiUrl . "/datastore_search_sql?sql=" . $query;

	$data = doCurl($urlQuery, $ckanApiKey);
	if (!checkDatastoreSearchResult($data)) {
		return [];
	}
    $recordsArray = [];
    if (isset($data->result->records)) {
        $recordsArray = $data->result->records;
    }

    $locationReducedArray = [];
	foreach ($recordsArray as $recordKey => $record) {
		$station = array();
		$station['id'] = $record->BPUIC;
		$station['sloid'] = $record->SLOID;
		$station['name'] = $record->BEZEICHNUNG_OFFIZIELL;
		$iers = null;
		if ($record->Z_WGS84) {
			$iers = $record->Z_WGS84;
		}
		$station['coordinate'] = [
			"type" 		=> "WGS84",
            "latitude" 	=> $record->N_WGS84,
			"longitude" => $record->E_WGS84,
			"IERS" 		=> $iers,
		];
        $station['distance'] = haversineGreatCircleDistance($x, $y, $record->N_WGS84, $record->E_WGS84);
		$locationReducedArray[$recordKey] = $station;
	}

	setbuffer('getLocationCoordinates', $x . "---" . $y, $locationReducedArray); // buffering
	return $locationReducedArray;
}

/**
* Gets the first location in the list. Be aware, that no special sorting is implemented
* @param $name - the name of the station
* @returns the first location or null
* @TODO better sorting
*/		
function getFirstLocationFull($apiKey, $ckanApiUrl, $name) {
	$loc_struct = getLocation($apiKey, $ckanApiUrl, $name);
	$n = count($loc_struct);
	$log = json_encode($loc_struct);

	if (count($loc_struct) > 0) {
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
function getFirstLocation($apiKey, $ckanApiUrl, $name) {
	$loc_struct = getLocation($apiKey, $ckanApiUrl, $name);
	$n = count($loc_struct);
	$log = json_encode($loc_struct);

    if (count($loc_struct) > 0) {
        $id = $loc_struct[0]['id'];
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
function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
}

?>
