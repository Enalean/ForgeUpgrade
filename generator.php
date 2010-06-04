<?php

  /*foreach($argv as $arg) {
    
    }*/

$name = $argv[$argc - 1];


// Set the default timezone to use. 
date_default_timezone_set('UTC');

$filename = date('YmdHi', $_SERVER['REQUEST_TIME']).'_'.unCapitalize($name).'.php';

$tmpl = file_get_contents(dirname(__FILE__).'/templates/DefaultBucket.tmpl.php');
$tmpl = preg_replace('/%className%/', $name, $tmpl);

// Directory
$dir = date('Y', $_SERVER['REQUEST_TIME']);
if (!is_dir($dir)) {
    mkdir($dir);
}
file_put_contents($dir.'/'.$filename, $tmpl);




function unCapitalize($name) {
    if (preg_match('%^([A-Z][^A-Z]*)([A-Z].*)%', $name, $matches)) {
        $res = strtolower($matches[1]);
        if (isset($matches[2])) {
            return $res.'_'.unCapitalize($matches[2]);
        }
        return $res;
    } elseif(preg_match('%^([A-Z][^A-Z]*)$%', $name, $matches)) {
        return strtolower($matches[1]);
    }
    return '';
}

?>