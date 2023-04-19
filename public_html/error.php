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
* Custom Error function. Ignors E_WARNING and E_NOTICE
* @param $errno - Error Level.
* @param $errstr - The error text
* @param $errfile - The file the error occurred in 
* @param $errline - The line the error occurred in
* @param $errctxt - The error context
* @returns nothing

*/
function customError($errno,$errstr,$errfile,$errline,$errorctxt){
	if ($errno>E_NOTICE){
		echo"<b>Error:</b> [$errno] $errstr<br>";
		echo"<b>File:</b> [$errno] $errfile<br>";
		echo"<b>Line:</b> [$errno] $errline<br>";
		echo"<b>Context:</b> [$errno] $errctxt<br>";
		echo"Ending Script";
		die();
	}
}


/**
* Used for logs. Dums the content into a file (not appending)
* @param $filename - The filename
* @param $var - The content to dump into the file
* @returns nothing
*/
function var_dump_file($filename, $var, $append=false){
  $message=var_export($var, true);
  if ($append) {
	  $handle = fopen($filename, "a");
  }
  else {
	  $handle = fopen($filename, "w");
  }
  fwrite($handle, $message);
  fclose($handle);
}

/**
* Writes output to the console
* @param $str - String to write to the console
* @returns nothing
*/
function mystdout($str){
file_put_contents("php://stdout", "\nLog:\n$str\n");
}

/**
* Writes the content of a variable with echo
* @param $str - The variable
* @returns nothing
*/
function mylog($str){
echo "\nLog";
echo $str;
var_export($str, true);
echo "\n";

}

//setting the error handler
set_error_handler("customError");


?>