<?php

/**
* The function replaces the date in the xml stream
* @param $datetime - the datetime to set
* @param $searchstr - The string that indicates, where to put it
* @param $xml - the query string, where the replacement shall be made
* @returns the xml with the datetime replaced. If the datetime is invalid, then it is omitted
*/
function replaceTime($datetime, $searchstr, $xml) {
    if (isset($datetime)) {
        echo validateDate($datetime);
        if (validateDate($datetime)){
            $mytime = getTheDate($datetime)->format("Y-m-d\TH:i:s");
            $xml = str_replace($searchstr, $mytime, $xml);
        } else {
            $xml=str_replace($searchstr, "", $xml);
        }
    } else {
        $xml = str_replace($searchstr, "", $xml);
    }
        
    return $xml;
}

/**
* The function sets the date to the appropriate format
* @param $date - the datetime to use
* @param $format - The format to use. Is automatically set to "YYYY-MM-DDTHH:MM:SS"
* @returns the date in the currect format or null
*/
// Y-m-d\TH:i:s
function getTheDate($date, $format = 'Y-m-d\TH:i:s') {
    $d = DateTime::createFromFormat($format, $date);
    return $d;
}

/**
* The function checks if the date is valid.
* @param $date - the datetime to use
* @param $format - The format to use. Is automatically set to "YYYY-MM-DDTHH:MM:SS"
* @returns true, when the date is valid
*/
function validateDate($date, $format = 'Y-m-d\TH:i:s') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

/**
* The function builds a datetime from a date and a time
* @param $date - the date part
* @param $time  - the time part. Can be null (=> results in 00:00:00 to be used)
* @returns the date in the currect format or null
*/
function builddTime($date, $time) {
	if ($date == null) {
		return null;
	}
    if (strlen($time) !== 8) {
		trigger_error("Please give the time in following format: H:i:s inlducing seconds.", E_USER_ERROR);
        return false;
	}
	if ($time == null) {
		$time = "00:00:00";
	}
	$str = trim($date) . "T" . trim($time);
	return getTheDate($str);
}


/**
* The function prettyprints json
* @param $json - Input json
* @returns pretty printed json as a string
* @see - http://stackoverflow.com/questions/6054033/pretty-printing-json-with-php
*/
function prettyPrint($json) {
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen($json);

    for ($i = 0; $i < $json_length; $i++) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if ($ends_line_level !== NULL) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ($in_escape) {
            $in_escape = false;
        } else if ($char === '"') {
            $in_quotes = !$in_quotes;
        } else if (!$in_quotes) {
            switch($char) {
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
                default:
                    break;
            }
        } else if ($char === '\\') {
            $in_escape = true;
        }
        if ($new_line_level !== NULL) {
            $result .= "\n" . str_repeat("\t", $new_line_level);
        }
        $result .= $char . $post;
    }

    return $result;
}


/**
 * Check if string contains valid xml
 *
 * @param string $xmlStr
 * @return string | boolean
 */
function validXmlString($xmlStr) {
    $errorsString = '';
    if (strlen($xmlStr) <= 0) {
        $errorsString .= 'xml string is empty';
    }
    $previous = libxml_use_internal_errors(true);

    $document = simplexml_load_string($xmlStr);
    $xml = explode("\n", $xmlStr);

    if (!$document) {
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        foreach ($errors as $errorKey => $error) {
            if (isset($error->message)) {
                $errorsString .= $error->message;
            }
        }
        return $errorsString;
    }
    return true;
}

/**
 * Convert XML to an Array
 *
 * @param string $XML
 * @return array
 */
function XMLtoArray($XML) {
    $xml_parser = xml_parser_create();
    xml_parse_into_struct($xml_parser, $XML, $vals);
    xml_parser_free($xml_parser);
    // wyznaczamy tablice z powtarzajacymi sie tagami na tym samym poziomie
    $_tmp='';
    foreach ($vals as $xml_elem) {
        $x_tag=$xml_elem['tag'];
        $x_level=$xml_elem['level'];
        $x_type=$xml_elem['type'];
        if ($x_level!=1 && $x_type == 'close') {
            if (isset($multi_key[$x_tag][$x_level]))
                $multi_key[$x_tag][$x_level]=1;
            else
                $multi_key[$x_tag][$x_level]=0;
        }
        if ($x_level!=1 && $x_type == 'complete') {
            if ($_tmp==$x_tag)
                $multi_key[$x_tag][$x_level]=1;
            $_tmp=$x_tag;
        }
    }

    // jedziemy po tablicy
    foreach ($vals as $xml_elem) {
        $x_tag=$xml_elem['tag'];
        $x_level=$xml_elem['level'];
        $x_type=$xml_elem['type'];
        if ($x_type == 'open')
            $level[$x_level] = $x_tag;
        $start_level = 1;
        $php_stmt = '$xml_array';
        if ($x_type=='close' && $x_level!=1)
            $multi_key[$x_tag][$x_level]++;
        while ($start_level < $x_level) {
            $php_stmt .= '[$level['.$start_level.']]';
            if (isset($multi_key[$level[$start_level]][$start_level]) && $multi_key[$level[$start_level]][$start_level])
                $php_stmt .= '['.($multi_key[$level[$start_level]][$start_level]-1).']';
            $start_level++;
        }
        $add='';
        if (isset($multi_key[$x_tag][$x_level]) && $multi_key[$x_tag][$x_level] && ($x_type=='open' || $x_type=='complete')) {
            if (!isset($multi_key2[$x_tag][$x_level]))
                $multi_key2[$x_tag][$x_level]=0;
            else
                $multi_key2[$x_tag][$x_level]++;
            $add='['.$multi_key2[$x_tag][$x_level].']';
        }
        if (isset($xml_elem['value']) && trim($xml_elem['value'])!='' && !array_key_exists('attributes', $xml_elem)) {
            if ($x_type == 'open')
                $php_stmt_main=$php_stmt.'[$x_type]'.$add.'[\'content\'] = $xml_elem[\'value\'];';
            else
                $php_stmt_main=$php_stmt.'[$x_tag]'.$add.' = $xml_elem[\'value\'];';
            eval($php_stmt_main);
        }
        if (array_key_exists('attributes', $xml_elem)) {
            if (isset($xml_elem['value'])) {
                $php_stmt_main=$php_stmt.'[$x_tag]'.$add.'[\'content\'] = $xml_elem[\'value\'];';
                eval($php_stmt_main);
            }
            foreach ($xml_elem['attributes'] as $key=>$value) {
                $php_stmt_att=$php_stmt.'[$x_tag]'.$add.'[$key] = $value;';
                eval($php_stmt_att);
            }
        }
    }
    return $xml_array;
}

/**
* Changes array keys
* @param $array - array of which the keys should be changed
* @param $oldKeys - array of the keys to change
* @param $newKeys - array of the new keys
* @returns changed array
* TODO: have only one $keys array with old keys as key and new key as value
*/
function readableArrayKeys($array, $oldKeys, $newKeys) {
    if (count($oldKeys) !== count($newKeys)) {
        trigger_error("Old keys array and new keys array should be the same length.", E_USER_ERROR);
        return false;
    }

    $returnArray = [];
    foreach ($array as $arrayKey => $arrayEntry) {
        if (in_array($arrayKey, $oldKeys)) {
            $oldKey = $arrayKey;
            $newArrayKey = array_search($arrayKey, $oldKeys);
            $newKey = $newKeys[$newArrayKey];
        } else {
            $oldKey = $arrayKey;
            $newKey = $arrayKey;
        }

        if (is_array($arrayEntry)) {
            $returnArray[$newKey] = readableArrayKeys($arrayEntry, $oldKeys, $newKeys);
        } else if (gettype($arrayEntry) === 'string') {
            $returnArray[$newKey] = $arrayEntry;
            
            $oldDeleteKey = array_search($oldKey, $oldKeys);
            unset($oldKeys[$oldDeleteKey]);
            $newDeleteKey = array_search($newKey, $newKeys);
            unset($newKeys[$newDeleteKey]);
        }
    }
    return $returnArray;
}

?>
