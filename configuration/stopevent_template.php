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

/**
The template is the XML request with "variables" in the form of %%%VAR%%%.
@TODO: Currently vars can be with or withouth surrounding XML tags. This should be changed
*/
$stopeventxml = <<<END
<Trias version="1.1" xmlns="http://www.vdv.de/trias"  xmlns:siri="http://www.siri.org.uk/siri"  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"> 
 <ServiceRequest> 
 <siri:RequestTimestamp>2016-08-09T13:29:00</siri:RequestTimestamp> 
 <siri:RequestorRef>EPSa</siri:RequestorRef>
 <RequestPayload>
 <StopEventRequest>
 <Location>
 <LocationRef>
 <StopPointRef>%%%BPUIC%%%</StopPointRef>
 </LocationRef>
 %%%TIME%%% 
 </Location>
 <Params>
 <NumberOfResults>%%%LIMIT%%%</NumberOfResults>
 <StopEventType>%%%TYPE%%%</StopEventType>
 <IncludePreviousCalls>%%%bPC%%%</IncludePreviousCalls>
 <IncludeOnwardCalls>%%%bOC%%%</IncludeOnwardCalls>
 <IncludeRealtimeData>%%%bRT%%%</IncludeRealtimeData>
 </Params>
 </StopEventRequest>
 </RequestPayload>
 </ServiceRequest>
 </Trias>
 
END;

$mapper = array();
$mapper['bpuic']=[ "r" => "%%%BPUIC%%%", "w" => false];
$mapper['time']=[ "r" => "%%%TIME%%%", "w" => true];
$mapper['limit']=[ "r" => "%%%LIMIT%%%", "w" => false];
$mapper['type']=[ "r" => "%%%TYPE%%%", "w" => false];
$mapper['bPC']=[ "r" => "%%%bPC%%%", "w" => false];
$mapper['bOC']=[ "r" => "%%%bOC%%%", "w" => false];
$mapper['bRT']=[ "r" => "%%%bRT%%%", "w" => false];

$form =<<<END
<form id="usrform" >
StopPointRef:  <input type="text" id="bpuic" required>  (e.g. 8507000) <br>

StopEventType <select id="type" required>
<option value="arrival">Arrival</option>
<option value="departure">Departure</option>
</select>
<br>
DepArrTime <input type="text" id="time">/input>
IncludePreviousCalls <input type="checkbox" id="bPC"></input><br>
IncludeOnwardCalls <input type="checkbox" id="bOC"></input><br>
IncludeRealtimeData <input type="checkbox" id="bRT"></input><br>

Your API-Key (obtain it <a href="https://opentransportdata.swiss/dev-dashboard/">here</a>): <input type="text" id="apikey" required >  <br>

<input type="button" value="Search StopEvent for now" onClick="doStopRequest('bpuic','time','type','bPC','bOC','bRT','limit',''req','status','resp','jresp','apikey')">
<p>
Request:<p>
<textarea id="req" rows="10" cols="100" ></textarea>

<p>Response:        Status: <input type="text" id="status" disabled> 
<p>
<textarea id="resp"  rows="10" cols="100" ></textarea> 
<p> 
Response in JSON (simplified): <p>
<textarea id="jresp"  rows="10" cols="100"  ></textarea> 
<p>
<input type="button" value="Search Station in CKAN" onClick="buildOptionsStations('mySelect','name')">

</form>
END;
// test 
?>