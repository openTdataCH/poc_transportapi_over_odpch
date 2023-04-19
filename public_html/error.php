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
function customError($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting, so let it fall
        // through to the standard PHP error handler
        return false;
    }

    // $errstr may need to be escaped:
    $errstr = htmlspecialchars($errstr);

    switch ($errno) {
        case E_USER_ERROR:
            echo "<b>ERROR</b> [$errno] $errstr<br />\n";
            echo "  Fatal error on line $errline in file $errfile";
            echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
            echo "Aborting...<br />\n";
            exit(1);

        case E_USER_WARNING:
            echo "<b>WARNING</b> [$errno] $errstr<br />\n";
            break;

        case E_USER_NOTICE:
            echo "<b>NOTICE</b> [$errno] $errstr<br />\n";
            break;

        default:
            echo "Unknown error type: [$errno] $errstr<br />\n";
            break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}

/**
* Used for logs. Dums the content into a file (not appending)
* @param $filename - The filename
* @param $var - The content to dump into the file
* @returns nothing
*/
function var_dump_file($filename, $var, $append=false) {
  $message = var_export($var, true);
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
function mystdout($str) {
    file_put_contents("php://stdout", "\nLog:\n$str\n");
}

/**
* Writes the content of a variable with echo
* @param $str - The variable
* @returns nothing
*/
function mylog($str) {
    echo "\nLog";
    echo $str;
    var_export($str, true);
    echo "\n";
}

// setting the error handler
set_error_handler("customError");

?>
