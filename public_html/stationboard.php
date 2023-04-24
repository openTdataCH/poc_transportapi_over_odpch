<?php
/*
    Copyright 2016 Matthias G�nter, GnostX GmbH

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
include_once "helpers.php";
include_once "location.php";
include_once __DIR__ . "../transport-api-env/configuration.php";
include_once __DIR__ . "../transport-api-env/stopevent_template.php";

/**
* Gets a stationboard from the TRIAS interface of opentransportdata.swiss and returns a transport-API conform answer
* @param $id - the bpuic to use
* @param $limit - the number of public transport items
* $param $dateTime - the time of interest
* $param $isDeparture - true = departure, false arrival
* @returns a transport-API conform answer with all information that is in the opentransport-system
*/
function getStationBoard($id, $limit, $dateTime, $isDeparture, $queryInfo = null) {
    global $stopeventxml, $apiKey, $apiUrl;

    $xml = $stopeventxml;
    $xml = str_replace("%%%BPUIC%%%", $id, $xml);

    if ($limit > 90 or $limit < 1) {
        $limit = 30;
    }
    $xml = str_replace("%%%LIMIT%%%",$limit,$xml);

    if (isset($dateTime)) {
        if (validateDate($dateTime)) {
            $mytime = '<DepArrTime>' . getTheDate($dateTime)->format("Y-m-d\TH:i:s") . '</DepArrTime>';
            $xml = str_replace("%%%TIME%%%" , $mytime, $xml);
        } else {
            trigger_error('The provided datetime parameter does not seem to be a valid date. Please choose a date format of "Y-m-d\TH:i:s"', E_USER_ERROR);
            return false;
        }
    } else {
        $xml = str_replace("%%%TIME%%%", "", $xml);
    }
        
    if ($isDeparture) {
        $xml = str_replace("%%%TYPE%%%", "departure", $xml);	
    } else {
        $xml = str_replace("%%%TYPE%%%", "arrival", $xml);	
    }
    $xml = str_replace("%%%REQUEST_TIMESTAMP%%%", date('c'), $xml);
    $xml = str_replace("%%%bPC%%%", "false", $xml); // we don't need previous Calls
    $xml = str_replace("%%%bOC%%%", "false", $xml); // we don't need onwardCalls
    $xml = str_replace("%%%bRT%%%", "true", $xml);

    $result = do_curl_trias_api($apiUrl, $xml, $apiKey);

    $xmlValidation = validXmlString($result);
    if (gettype($xmlValidation) === 'string') {
        trigger_error("Results does not contain valid XML. Check the sent XML. XML errors: " . $xmlValidation, E_USER_ERROR);
        return false;
    }

    $xmlArray = XMLtoArray($result);
    if (!is_array($xmlArray)) {
        trigger_error("xmlArray is not an array. Check the XML", E_USER_ERROR);
        return false;
    }

    if (isset($xmlArray['TRIAS:TRIAS']['TRIAS:SERVICEDELIVERY']["TRIAS:DELIVERYPAYLOAD"]["TRIAS:STOPEVENTRESPONSE"]["TRIAS:ERRORMESSAGE"])) {
        $apiError = $xmlArray['TRIAS:TRIAS']['TRIAS:SERVICEDELIVERY']["TRIAS:DELIVERYPAYLOAD"]["TRIAS:STOPEVENTRESPONSE"]["TRIAS:ERRORMESSAGE"]["TRIAS:TEXT"]["TRIAS:TEXT"];
        echo 'The API returned an error: ' . $apiError . '. Maybe the station name is not specific enough (use e.g. /locations?query=Basel to find a more specific station) or the provided date is in the past or too far in the future.';
        return false;
    }

    $xmlArrayItems = $xmlArray['TRIAS:TRIAS']['TRIAS:SERVICEDELIVERY']['TRIAS:DELIVERYPAYLOAD']['TRIAS:STOPEVENTRESPONSE']['TRIAS:STOPEVENTRESULT'];

    // readableArrayKeys needs the order of keys appearing
    // sorry, this is quite unreadable. TODO: readableArrayKeys should accept one $keys array with old key as key and new key as value
    $resultArray = readableArrayKeys($xmlArrayItems,
        ['TRIAS:RESULTID', 'TRIAS:STOPEVENT', "TRIAS:THISCALL", "TRIAS:CALLATSTOP", "TRIAS:STOPPOINTREF", "TRIAS:STOPPOINTNAME", "TRIAS:TEXT", "TRIAS:LANGUAGE", "TRIAS:PLANNEDBAY", "TRIAS:ESTIMATEDBAY", "TRIAS:SERVICEARRIVAL", "TRIAS:SERVICEDEPARTURE", "TRIAS:TIMETABLEDTIME", "TRIAS:ESTIMATEDTIME", "TRIAS:STOPSEQNUMBER", "TRIAS:SERVICE", "TRIAS:OPERATINGDAYREF", "TRIAS:JOURNEYREF", "TRIAS:LINEREF", "TRIAS:DIRECTIONREF", "TRIAS:MODE", "TRIAS:PTMODE", "TRIAS:RAILSUBMODE", "TRIAS:NAME", "TRIAS:PUBLISHEDLINENAME", "TRIAS:OPERATORREF", "TRIAS:ATTRIBUTE", "TRIAS:CODE", "TRIAS:ORIGINSTOPPOINTREF", "TRIAS:ORIGINTEXT", "TRIAS:DESTINATIONSTOPPOINTREF", "TRIAS:DESTINATIONTEXT"],
        ['result_id', 'stop_event', 'call', 'call_stop', 'stop_point_reference', 'stop_name', 'text', 'language', 'planned_track', 'estimated_track', 'arrival', 'departure', 'date_time', 'estimated_time', 'stops', 'service', 'operating_date', 'journey_reference', 'line_reference', 'direction', 'mode', 'pt_mode', 'sub_mode', 'name', 'published_line_name', 'operation_reference', 'attribute', 'code', 'origin_stop_reference', 'origin_text', 'destination_stop_reference', 'destination_text']
    );

	$returnArray['stationboard'] = [];
    if ($queryInfo) {
        $returnArray['stationboard']['info'] = $queryInfo;
    }
    $returnArray['stationboard']['results'] = $resultArray;

	return json_encode($returnArray);
}
		  
/**
* Gets a stationboard from the TRIAS interface of opentransportdata.swiss and returns a transport-API conform answer
* @TODO the current system does not use the necessary priorisation of the station. It just takes the first one
* @param $station - the bpuic to use
* @param $limit - the number of public transport items
* $param $dateTime - the time of interest
* $param $isDeparture - true = departure, false arrival
* @returns a transport-API conform answer with all information that is in the opentransport-system or false
*/
function getStationBoardByName($ckanApiKey, $ckanApiUrl, $station, $limit, $dateTime, $isDeparture) {
	$loc_struct = getLocation($ckanApiKey, $ckanApiUrl, $station);
	$n = count($loc_struct);
	$log = json_encode($loc_struct);

    if (count($loc_struct) > 0) {
        if (count($loc_struct) > 1) {
            $queryInfo = 'Stationboard station location search contains more than one result. Number of results: ' . count($loc_struct) . '. The first appearing station with the BPUIC "' . $loc_struct[0]['id'] . '" was taken, with the name "' . $loc_struct[0]['name'] . '". If you did not found your desired station, please search more specific like "?station=Bern Wankdorf Bahnhof".';
        }
        $id = $loc_struct[0]['id'];
        return getStationBoard($id, $limit, $dateTime, $isDeparture, $queryInfo);
	}

	$res = array();
	return $res; 
}

?>
