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
* The function docurl is used accesses the CKAN API from opentransportdata.swiss
* But it is a general function that connects with GET to the URL and returns the Json
* @param $url - the URL to connect to
* @returns the json object
*/


function docurl($url){
	$ch =curl_init();

	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_HEADER,0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	

	$json_response="";
	$r =curl_exec($ch);
	//var_dump_file("r.res",$r);
	if ($r == false) {
		echo $url."\n";
		echo "failed get\n";
		echo curl_error($ch);
		}
	else{
		$json_response =json_decode($r);	
	}
	curl_close($ch);
	return $json_response;  //TODO
}

/**
* The function accesses the URL from a trias end point with the xml request and the api key needed
* @param $url - the URL of the trias end point
* @param $xml - the XML of the request (needs to be VDV 431)
* @param $apikey - the API key to used
* @returns the xml
* @see https://opentransportdata.swiss
*/
function do_curl_trias_api($url, $xml,$apikey){
	
	$ch =curl_init();
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_URL,$url);

	$headers =array(
	  'Content-type: application/xml',
	  'Authorization: '.$apikey
	  );
	curl_setopt($ch, CURLOPT_HEADER,0);
	curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$xml);
	$xml_response="";
	$r =curl_exec($ch);
	//echo $r;
	if ($r == false) {
		echo $url."\n";
		echo $xml."\n";
		echo "failed get\n";
		echo curl_error($ch);
		}
	else{
		$xml_response =$r;	
	}
	curl_close($ch);
	return $xml_response;  
}



/**
* The function replaces the date in the xml stream
* @param $datetime - the datetime to set
* @param $searchstr - The string that indicates, where to put it
* @param $xml - the query string, where the replacement shall be made
* @returns the xml with the datetime replaced. If the datetime is invalid, then it is omitted
*/
function replacetime($datetime,$searchstr,$xml){

if (isset($datetime)){
	echo validateDate($datetime);
	if (validateDate($datetime)){
		$mytime="<DepArrTime>" . getTheDate($datetime)->format("Y-m-d\TH:i:s") . "</DepArrTime>";
		$xml=str_replace($searchstr,$mytime,$xml);
	} else {
		$xml=str_replace($searchstr,"",$xml);
	}
} else {
			$xml=str_replace($searchstr,"",$xml);
	}
	
return $xml;
}

/**
* The function sets the date to the appropriate format
* @param $date - the datetime to use
* @param $format - The format to use. Is automatically set to "YYYY-MM-DDTHH:MM:SS"
* @returns the date in the currect format or null
*/

function getTheDate($date, $format = 'Y-m-d\TH:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
	//echo "getTheDate:".$d->format($format)."\n";
    return $d;
}

/**
* The function checks if the date is valid.
* @param $date - the datetime to use
* @param $format - The format to use. Is automatically set to "YYYY-MM-DDTHH:MM:SS"
* @returns true, when the date is valid
*/
function validateDate($date, $format = 'Y-m-d\TH:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
	//echo "validateDate:".$d->format($format)."\n";

    return $d && $d->format($format) == $date;
}

/**
* The function builds a datetime from a date and a time
* @param $date - the date part
* @param $time  - the time part. Can be null (=> results in 00:00:00 to be used)
* @returns the date in the currect format or null
*/

function  builddtime($date,$time){
	if ($date==null){
		return null;
	}
	if ($time==null){
		$time="00:00:00";
	}
	$str = trim($date)."T".trim($time);
	return getTheDate($str);
}


/**
* The function prettyprints json
* @param $json - Input json
* @returns pretty printed json as a string
* @see - http://stackoverflow.com/questions/6054033/pretty-printing-json-with-php
*/

function prettyPrint( $json )
{
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ( $in_escape ) {
            $in_escape = false;
        } else if( $char === '"' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ( $char === '\\' ) {
            $in_escape = true;
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
    }

    return $result;
}

?>