<?php
header('Content-type: application/json');
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


//now. http://blogs.shephertz.com/2014/05/21/how-to-implement-url-routing-in-php/

include_once 'location.php';
include_once 'stationboard.php';
include_once 'connections.php';


    //return json_encode("this worked");
	/*
	The following function will strip the script name from URL i.e.  http://www.something.com/search/book/fitzgerald will become /search/book/fitzgerald
	*/
	function getCurrentUri()
	{
		$basepath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
		$uri = substr($_SERVER['REQUEST_URI'], strlen($basepath));
		if (strstr($uri, '?')) $uri = substr($uri, 0, strpos($uri, '?'));
		$uri = '/' . trim($uri, '/');
		return $uri;
	}

	$base_url = getCurrentUri();
	//file_put_contents("php://stdout", "\nbase url: >$base_url<");
	$routes1 = array();
	$routes1 = explode('/', $base_url);
	$routes= array();
	foreach($routes1 as $route)
	{
		if(trim($route) != '')
			array_push($routes, $route);
	}

	//$log=var_export($routes,true);
	//file_put_contents("php://stdout", "\n$log");
	$j=count($routes)-1;
	/*
	Now, $routes will contain all the routes. $routes[0] will correspond to first route. For e.g. in above example $routes[0] is search, $routes[1] is book and $routes[2] is fitzgerald
	*/
    //file_put_contents("php://stdout", "\nroutes[$j] : >$routes[$j]<");
	
	// doing locations
	if($routes[$j] == "locations")
	{
		$query=null;
		$res="";
		if (isset($_GET["query"])){
			$query=$_GET["query"];
			file_put_contents("php://stdout", "\nQuery found: ".$query);
		} 
		$x=null;
		if (isset($_GET["x"])){
			$x =$_GET["x"];
			}
		$y=null;
		if (isset($_GET["y"])){
			$y=$_GET["y"];
			}
		$res="";
		if (isset($query)){
		   $res= getlocationjson($query);	
		
		} else if (isset($x) and isset($y))
		{
			$res =getlocationjsoncoord($x,$y);
		} else {

		}
        if (empty($res)){
			//file_put_contents("php://stdout", "\nno result");
			$res=json_encode("No valid result");
		}
	    var_dump_file("coord.res",$res);
		$json=json_encode($res, JSON_PRETTY_PRINT);
		//file_put_contents("php://stdout", "\nDas Resultat: $json");
		echo prettyPrint($res);
		return;
	
	} else 	if($routes[$j] == "connections") {
		
	  $startbpuic=null;
	  if (isset($_GET["from"])){
		  $from=$_GET["from"];
		  $startbpuic=getfirstlocation($from);
		}
	  $stopbpuic=null;
	  if (isset($_GET["to"])){
		  $to=$_GET["to"];
		  $stopbpuic=getfirstlocation($to);
		}		
	  $date=null;
	  if (isset($_GET["date"])){
		  $date=$_GET["date"];
		}		
	  $time=null;
	  if (isset($_GET["time"])){
		  $time=$_GET["time"];
		}		
	  if (isset($_GET["isArrivalTime"])){
		  $isATime=$_GET["isArrivalTime"];
		}	
	  $limit=4;
	  if (isset($_GET["limit"])){
		  $limit=$_GET["limit"];
		}	
      builddtime($date,$time);
	  $direct=1;
	  if (($startbpuic == null) || ($stopbpuic ==null)){
		  return "{error: {'Start or stop not found'}}";
	  }
	  if ($isATime){
		  $res= getconnections($startbpuic,$stopbpuic,$dtime,null,$limit);
	  } else {
		  $res = getconnections($startbpuic,$stopbpuic,null,$dtime,$limit);
	  }
		  if (empty($res)){
					 echo ('{"error": {"Result empty"}}');
					 return;
		}


		echo prettyPrint($res);
		return; 
		
		
	} else 	if($routes[$j] == "stationboard")
	{
	  $id=null;
	  if (isset($_GET["id"])){
		  $id=$_GET["id"];
		}
	  $station=null;
	  if (isset($_GET["station"])){
		  $station=$_GET["station"];
		}
	  $limit=0;
	  if (isset($_GET["limit"])) {
		  $limit=$_GET["limit"];
		}
	  $datetime=null;
	  if (isset($_GET["datetime"])){
		  $datetime=$_GET["datetime"];
	  }
	  $type=null;
	  if (isset($_GET["type"])){
		  $type=$_GET["type"];
	  }
	  $isdeparture=true;
	  if (strcmp($type,"arrival")==0){
		  $isdeparture=false;
	  }
	  if (isset($_GET["id"])){
		  //file_put_contents("php://stdout", "\nBy...id");
		  $res= getstationboard($id,$limit,$datetime,$isdeparture);
		 
	  } else if (isset($_GET["station"])){
		  //file_put_contents("php://stdout", "\nBy...name");
		  $res= getstationboardbyname($station,$limit,$datetime,$isdeparture);
	  } else{
		   echo ("{'error':{'Neither id nor station provided.'}}");
		   return;
	  }
	  
	  if (empty($res)){
					 echo ("{'error':{'Result was empty'}}");
					 return;
		}


		echo prettyPrint($res);
		return;

		
	} 
		
     //file_put_contents("php://stdout", "\n$route not correct or parameter not set");
	echo ("{'error' : {'This route does not exist'}}");
	return;

		 

?>