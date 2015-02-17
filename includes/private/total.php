<?php

require_once('wp-load.php');

global $wpdb;

$qry = $wpdb->get_results("SELECT count(*) tot FROM new_count");
print $qry[0]->tot;

?>