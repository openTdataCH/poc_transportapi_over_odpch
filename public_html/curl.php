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

/**
* The function doCurl is used accesses the CKAN API from opentransportdata.swiss
* But it is a general function that connects with GET to the URL and returns the Json
* @param $url - the URL to connect to
* @returns the json object
*/
function doCurl($url, $apiKey) {
    if (null === $url) {
        echo "API URL is not set.";
        exit;
    }
    if (null === $apiKey) {
        echo "API key is not set.";
        exit;
    }

	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);

    $headers = array(
        'Content-Type: text/csv',
        'Authorization: ' . $apiKey
	);

	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$json_response = "";
	$response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	if ($httpCode !== 200) {
		echo $url . "\n";
        echo $response . "\n";
		echo "failed get\n";
		echo curl_error($ch);
	} else {
		$json_response = json_decode($response);	
	}

	curl_close($ch);
	return $json_response;
}

/**
* The function accesses the URL from a trias end point with the xml request and the api key needed
* @param $url - the URL of the trias end point
* @param $xml - the XML of the request (needs to be VDV 431)
* @param $apiKey - the API key to used
* @returns the xml
* @see https://opentransportdata.swiss
*/
function do_curl_trias_api($url, $xml, $apiKey) {
    if (null === $url) {
        echo "API URL is not set.";
        exit;
    }
    if (null === $apiKey) {
        echo "API key is not set.";
        exit;
    }

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_URL, $url);

	$headers = array(
        'Content-Type: application/XML',
        'Authorization: ' . $apiKey
	);

	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

	$xml_response = "";
	$response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	if ($httpCode !== 200) {
		echo $url . "\n";
		echo $xml . "\n";
        echo $response . "\n";
		echo "failed get\n";
		echo curl_error($ch);
        return '';
	} else {
		$xml_response = $response;	
	}

	curl_close($ch);
	return $xml_response;  
}

?>
