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

//As we are session based, we don't need a buffer that expires
$bigbuffer = array(); //array used to buffer requests from CKAN requests

/*
Checks if the request is already in the buffer
* @param $fct - the function used
* @param $query - the query asked
* @returns true, when the buffer contains the value
*/
function inbuffer($fct, $query) {
	global $bigbuffer;	
	if (isset($bigbuffer[$fct . "##" . $query])){
		return true;
	}
	return false;
}

/*
Obtains the value from the buffer
* @param $fct - the function used
* @param $query - the query asked
* @returns the buffer value or null
*/
function getbuffer($fct, $query) {
	global $bigbuffer;	
	return $bigbuffer[$fct . "##" . $query];
}

/*
Sets the value in the buffer
* @param $fct - the function used
* @param $query - the query asked
* @returns no return value
*/
function setbuffer($fct, $query, $val) {
	global $bigbuffer;
	$bigbuffer[$fct . "##" . $query] = $val;	
}

?>
