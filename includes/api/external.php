<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if($_SERVER['REQUEST_METHOD'] != "POST") die("Invalid use of API");

require_once('../../../../../wp-load.php');

if (get_option('newstatpress_externalapi')!='checked') die("API not activated");;

// get the parameter from URL
$var = $_REQUEST["VAR"];
$key = $_REQUEST["KEY"];  # key readed is md5(date('m-d-y H i')+'Key')

if( !preg_match("/^[a-zA-Z0-9 ]*$/",$key) ) die("Invalid key");

if ($var == null && $key == null) die("API needs parameters");

global $wpdb;
$table_name = $wpdb->prefix . "statpress";

// read key from wordpress option
$api_key=get_option('newstatpress_apikey');
$api_key=md5(gmdate('m-d-y H i')+$api_key);


// test if can use API
if ($key != $api_key)  die("Not authorized API access.");

// test all vars
if ($var=='version') {
  echo json_encode(
    array(
      $var => $_NEWSTATPRESS['version']
    )
  );
}

?> 



