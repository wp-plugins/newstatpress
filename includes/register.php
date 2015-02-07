<?php

require_once('wp-load.php');
#require_once('wp-includes/formatting.php');

global $wpdb;

$site=md5($_GET["site"]);
$ver=esc_sql($_GET["ver"]);

$sqlDel="DELETE FROM new_count WHERE MD5='$site'";
$sqlIns="INSERT INTO new_count ( MD5, VER) VALUES ( '$site', '$ver')";

$wpdb->query($sqlDel);
$wpdb->query($sqlIns);

?>