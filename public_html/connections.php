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
include_once "helpers.php";
include_once "location.php";
include_once __DIR__ . "../transport-api-env/configuration.php";
include_once __DIR__ . "../transport-api-env/triprequest_template.php";

/**
* Gets a stationboard from the TRIAS interface of opentransportdata.swiss and returns a transport-API conform answer
* @param $id - the bpuic to use
* @param $limit - the number of public transport items
* $param $datetime - the time of interest
* $param $isdeparture - true = departure, false arrival
* @returns a transport-API conform answer with all information that is in the opentransport-system
*/
function getConnections($startbpuic, $stopbpuic, $starttime, $stoptime, $numres, $queryInfo = null) {
    global $triprequestxml, $apiKey, $apiUrl;

    $xml = $triprequestxml;
    $xml = str_replace("%%%REQUEST_TIMESTAMP%%%", date('c'), $xml);
    $xml = str_replace("%%%StartBPUIC%%%", $startbpuic, $xml);
    $xml = str_replace("%%%StopBPUIC%%%", $stopbpuic, $xml);

    if ($numres > 90 || $numres < 1) {
        $numres = 30;
    }
    $xml = str_replace("%%%NumRES%%%", $numres, $xml);

    $xml = replacetime($starttime, "%%%StartDateTime%%%", $xml);
    $xml = replacetime($stoptime, "%%%StopDateTime%%%", $xml);

    $url = $apiUrl;
    if (null === $url) {
        echo "API URL is not set.";
        exit;
    }

    if (null === $apiKey) {
        echo "API key is not set.";
        exit;
    }

    $result = do_curl_trias_api($url, $xml, $apiKey); 
    file_put_contents("php://stdout", "\n$result");

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
        echo 'The API returned an error: ' . $apiError . '. Maybe the station names are not specific enough (use e.g. /locations?query=Basel to find more specific stations) or the provided date is in the past or too far in the future.';
        return false;
    }

    $xmlArrayItems = $xmlArray['TRIAS:TRIAS']['TRIAS:SERVICEDELIVERY']['TRIAS:DELIVERYPAYLOAD']['TRIAS:TRIPRESPONSE'];

    // readableArrayKeys needs the order of keys appearing
    // sorry, this is quite unreadable. TODO: readableArrayKeys should accept one $keys array with old key as key and new key as value
    $resultArray = readableArrayKeys($xmlArrayItems,
        ['TRIAS:TRIPRESPONSECONTEXT', 'TRIAS:SITUATIONS', "TRIAS:PTSITUATION", "SIRI:CREATIONTIME", "SIRI:VERSION", "SIRI:SOURCE", "SIRI:SOURCETYPE", "SIRI:UNKNOWNREASON", "SIRI:PRIORITY", "SIRI:SUMMARY", "TRIAS:TRIPRESULT", 'TRIAS:RESULTID', 'TRIAS:STOPEVENT', "TRIAS:THISCALL", "TRIAS:CALLATSTOP", "TRIAS:STOPPOINTREF", "TRIAS:STOPPOINTNAME", "TRIAS:TEXT", "TRIAS:LANGUAGE", "TRIAS:PLANNEDBAY", "TRIAS:SERVICEDEPARTURE", "TRIAS:TIMETABLEDTIME", "TRIAS:STOPSEQNUMBER", "TRIAS:SERVICE", "TRIAS:OPERATINGDAYREF", "TRIAS:JOURNEYREF", "TRIAS:LINEREF", "TRIAS:DIRECTIONREF", "TRIAS:MODE", "TRIAS:PTMODE", "TRIAS:RAILSUBMODE", "TRIAS:NAME", "TRIAS:PUBLISHEDLINENAME", "TRIAS:OPERATORREF", "TRIAS:ATTRIBUTE", "TRIAS:CODE", "TRIAS:ORIGINSTOPPOINTREF", "TRIAS:ORIGINTEXT", "TRIAS:DESTINATIONSTOPPOINTREF", "TRIAS:DESTINATIONTEXT", "TRIAS:TRIP", "TRIAS:TRIPID", "TRIAS:DURATION", "TRIAS:STARTTIME", "TRIAS:ENDTIME", "TRIAS:INTERCHANGES", "TRIAS:DISTANCE", "TRIAS:TRIPLEG", "TRIAS:LEGID", "TRIAS:TIMEDLEG", "TRIAS:LEGBOARD", "TRIAS:ESTIMATEDTIME", "TRIAS:LEGALIGHT", "TRIAS:SERVICEARRIVAL", "TRIAS:LEGTRACK", "TRIAS:TRACKSECTION", "TRIAS:TRACKSTART", "TRIAS:LOCATIONNAME", "TRIAS:TRACKEND", "TRIAS:DURATION", "TRIAS:LENGTH", "TRIAS:LEGINTERMEDIATES", "TRIAS:DEVIATION"],
        ['trip-context', 'situations', 'pt-situations', 'creation-time', 'version', 'source', 'source-type', 'unknown-reason', 'priority', 'summary', 'trip-result', 'result-id', 'stop-event', 'call', 'call-stop', 'stop-point-reference', 'stop-name', 'text', 'language', 'planned-track', 'departure', 'date-time', 'stops', 'service', 'operating-date', 'journey-reference', 'line-reference', 'direction', 'mode', 'pt-mode', 'sub-mode', 'name', 'published-line-name', 'operation-reference', 'attribute', 'code', 'origin-stop-reference', 'origin-text', 'destination-stop-reference', 'destination-text', 'trip', 'trip-id', 'duration', 'start-time', 'end-time', 'inter-changes', 'distance', 'trip-leg', 'leg-id', 'timed-leg', 'leg-board', 'estimated-time', 'legalight', 'service-arrival', 'track', 'track-section', 'track-start', 'location-name', 'track-end', 'duration', 'length', 'intermetiates', 'deviation'],
    );

    $returnArray['connections'] = [];
    if ($queryInfo) {
        $returnArray['connections']['info'] = $queryInfo;
    }
    $returnArray['connections']['results'] = $resultArray;

    return json_encode($returnArray);
}
	 
/**
* Builds a transportAPI checkpoint element
* @info - contains the intermediate format to built it
* @returns a transport-API conform checkpoint
*/		 
function buildCheckpoint($info) {
	$chk = array();

    $chk['station'] = getFirstLocationFull($info['station']); 
	$chk['arrival'] = $info['arrival'];
	$chk['departure'] = $info['departure'];
	$chk['platform'] = $info['plannedBay'];
	$chk['prognosis'] = buildPrognostic($info['estimatedBay'], $info['depprognostic'], $info['arrprognostic']);
	
	return $chk;
}
		 
/**
* Builds a transportAPI prognosis element
* @platform - the estimated platform (if it exists)
* @departure - estimated departure (if it exsits)
* @arrival - estimated arrival (if it exists)
* @returns a transport-API conform prognosis element
*/		 
function buildPrognostic($platform, $departure, $arrival) {
	$prog = array();

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
// INFO: this seems not to be used. If it should be used, it should be adjusted to the new API
function getConnectionsByName($start, $stop, $starttime, $stoptime, $numres) {
	file_put_contents("php://stdout", "\n the station: >$station<");
	
	$startbpuic = getFirstLocation($ckanApiKey, $ckanApiUrl, $start);
	$stopbpuic = getFirstLocation($ckanApiKey, $ckanApiUrl, $stop);
	if (isset($startbpuic) && isset($stopbpuic)) {
        return getConnections($startbpuic, $stopbpuic, $starttime, $stoptime, $numres);
	}

	$result = array();
	return $result; 
}

?>
