<?php

header('Content-type: application/json');

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

include_once 'location.php';
include_once 'stationboard.php';
include_once 'connections.php';
include_once __DIR__ . "../transport-api-env/configuration.php";

/*
The following function will strip the script name from URL i.e.  http://www.something.com/search/book/fitzgerald will become /search/book/fitzgerald
*/
function getCurrentUri() {
    $basepath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
    $uri = substr($_SERVER['REQUEST_URI'], strlen($basepath));
    if (strstr($uri, '?')) {
        $uri = substr($uri, 0, strpos($uri, '?'));
    }
    $uri = '/' . trim($uri, '/');
    return $uri;
}

$base_url = getCurrentUri();

$routes1 = array();
$routes1 = explode('/', $base_url);
$routes = array();

foreach ($routes1 as $route) {
    if (trim($route) != '') {
        array_push($routes, $route);
    }
}

if (count($routes) <= 0) {
    echo 'Please provide a request parameter. There are locations, connections and stationboard available.';
    exit;
}

$j = count($routes) - 1;

// doing locations
if ($routes[$j] == "locations") {
    $query = null;
    $res = "";
    if (isset($_GET["query"])) {
        $query = $_GET["query"];
        file_put_contents("php://stdout", "\nQuery found: " . $query);
    } 
    $x = null;
    if (isset($_GET["long"]) ){
        $x = $_GET["long"];
    }
    $y = null;
    if (isset($_GET["lat"])) {
        $y = $_GET["lat"];
    }
    $res = "";
    if (isset($query)) {
        $res = getLocationJson($ckanApiKey, $ckanApiUrl, $query);
    } else if (isset($x) && isset($y)) {
        $res = getLocationJsonCoordinates($ckanApiKey, $ckanApiUrl, $x, $y);
    }
    if (empty($res)) {
        $res = json_encode("No valid result");
    }

    var_dump_file("coord.res", $res);

    $json = json_encode($res, JSON_PRETTY_PRINT);
    echo prettyPrint($res);
    return;

} else if ($routes[$j] == "connections") {
    $startbpuic = null;
    if (isset($_GET["from"])) {
        $from = $_GET["from"];
        $startbpuic = getFirstLocation($ckanApiKey, $ckanApiUrl, $from);
    }

    $stopbpuic = null;
    if (isset($_GET["to"])) {
        $to = $_GET["to"];
        $stopbpuic = getFirstLocation($ckanApiKey, $ckanApiUrl, $to);
    }

    $date = null;
    if (isset($_GET["date"])) {
        $date = $_GET["date"];
    }

    $time = null;
    if (isset($_GET["time"])) {
        $time = $_GET["time"];
    }
    if (isset($time) && strlen($time) !== 8) {
		trigger_error("Please give the time in following format: H:i:s inlducing seconds.", E_USER_ERROR);
        return false;
	}

    $isATime = null;
    if (isset($_GET["isArrivalTime"])) {
        $isATime = $_GET["isArrivalTime"];
    }

    $limit = 4;
    if (isset($_GET["limit"])) {
        $limit = $_GET["limit"];
    }

    $direct = 1;
    if (($startbpuic == null) || ($stopbpuic ==null)) {
        return "{error: {'Start or stop not found'}}";
    }

    if ($isATime) {
        $res = getConnections($startbpuic, $stopbpuic, $time, null, $limit);
    } else {
        $res = getConnections($startbpuic, $stopbpuic, null, $time, $limit);
    }
    
    if (empty($res)) {
        echo('{"error": {"Result empty"}}');
        return;
    }

    echo prettyPrint($res);
    return; 
    
} else if ($routes[$j] == "stationboard") {
    $id = null;
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
    }

    $station = null;
    if (isset($_GET["station"])) {
        $station = $_GET["station"];
    }

    $limit = 0;
    if (isset($_GET["limit"])) {
        $limit = $_GET["limit"];
    }

    $datetime = null;
    if (isset($_GET["datetime"])) {
        $datetime = $_GET["datetime"];
    }

    $type = '';
    if (isset($_GET["type"])) {
        $type = $_GET["type"];
    }

    $isDeparture = true;
    if (strcmp($type, "arrival") == 0) {
        $isDeparture = false;
    }

    if (isset($_GET["id"])){
        $res = getStationBoard($id, $limit, $datetime, $isDeparture);
    } else if (isset($_GET["station"])) {
        $station = urlencode($station);
        $res = getStationBoardByName($ckanApiKey, $ckanApiUrl, $station, $limit, $datetime, $isDeparture);
    } else {
        echo ("{'error' : {'Neither id nor station provided.'}}");
        return;
    }
    
    if (empty($res)) {
        echo ("{'error' : {'Result was empty'}}");
        return;
    }

    echo prettyPrint($res);
    return;
} 
    
echo ("{'error' : {'This route does not exist'}}");
return;

?>
