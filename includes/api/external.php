<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if($_SERVER['REQUEST_METHOD'] != "POST") die("Invalid use of API");

require_once('../../../../../wp-load.php');

// get the parameter from URL
$var = $_REQUEST["VAR"];
$key = $_REQUEST["KEY"];

if( !preg_match("/^[a-zA-Z0-9 ]*$/",$key) ) die("Invalid key");

if ($var == null && $key == null) die("API needs parameters");

global $wpdb;
$table_name = $wpdb->prefix . "statpress";

// read key from wordpress option
$api_key=get_option('newstatpress_apikey');

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



