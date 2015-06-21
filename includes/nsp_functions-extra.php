<?php

/**
 * Display tabs pf navigation bar for menu in page
 *
 * @param menu_tabs list of menu tabs
 * @param current current tabs
 * @param ref page reference
 */
function nsp_DisplayTabsNavbarForMenuPage($menu_tabs, $current, $ref) {
    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $menu_tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?page=$ref&tab=$tab'>$name</a>";
    }
    echo '</h2>';
}


function nsp_DisplayTabsNavbarForMenuPages($menu_tabs, $current, $ref) {

    echo "<div id='usual1' class='icon32 usual'><br></div>";
    echo "<h2  class='nav-tab-wrapper'>";
    foreach( $menu_tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active selected' : '';
        echo "<a class='nav-tab$class' href='#$tab'>$name</a>";
    }
    echo '</h2>';
}

/**
 * Display data in table extracted from the given query
 *
 * @param fld GROUP BY argument of query
 * @param fldtitle title of field
 * @param limit quantity of elements to extract
 * @param param extra arguemnt for query (like DISTINCT)
 * @param queryfld field of query
 * @param exclude WHERE argument of query
 * @param print TRUE if the table is to print in page
 * @return return the HTML output accoding to the sprint state
 */
function nsp_GetDataQuery2($fld, $fldtitle, $limit = 0, $param = "", $queryfld = "", $exclude= "", $print = TRUE) {
  global $wpdb;
  $table_name = nsp_TABLENAME;

  if ($queryfld == '') {
    $queryfld = $fld;
  }
  $text = "<div class='wrap'><table class='widefat'>\n<thead><tr><th scope='col' class='keytab-head'><h2>$fldtitle</h2></th><th scope='col' style='width:20%;text-align:center;'>".__('Visits','newstatpress')."</th><th></th></tr></thead>\n";
  $rks = $wpdb->get_var("
     SELECT count($param $queryfld) as rks
     FROM $table_name
     WHERE 1=1 $exclude;
  ");

  if($rks > 0) {
    $sql="
      SELECT count($param $queryfld) as pageview, $fld
      FROM $table_name
      WHERE 1=1 $exclude
      GROUP BY $fld
      ORDER BY pageview DESC
    ";
    if($limit > 0) {
      $sql=$sql." LIMIT $limit";
    }
    $qry = $wpdb->get_results($sql);
    $tdwidth=450;

    // Collects data
    $data=array();
    foreach ($qry as $rk) {
      $pc=round(($rk->pageview*100/$rks),1);
      if($fld == 'nation') { $rk->$fld = strtoupper($rk->$fld); }
      if($fld == 'date') { $rk->$fld = nsp_hdate($rk->$fld); }
      if($fld == 'urlrequested') { $rk->$fld = nsp_DecodeURL($rk->$fld); }
      $data[substr($rk->$fld,0,250)]=$rk->pageview;
    }
  }

  // Draw table body
  $text .= "<tbody id='the-list'>";
  if($rks > 0) {  // Chart!

    if($fld == 'nation') { // Nation chart
      $charts=plugins_url('newstatpress')."/includes/geocharts.html".nsp_GetGoogleGeo($data);
    }
    else { // Pie chart
      $charts=plugins_url('newstatpress')."/includes/piecharts.html".nsp_GetGooglePie($fldtitle, $data);
    }

    foreach ($data as $key => $value) {
      $text .= "<tr><td class='keytab'>".$key."</td><td class='valuetab'>".$value."</td></tr>";
    }

    $text .= "<tr><td colspan=2 style='width:50%;'>
        <iframe src='".$charts."' class='framebox'>
          <p>[_e('This section requires a browser that supports iframes.]','newstatpress')</p>
        </iframe></td></tr>";
  }
  $text .= "</tbody></table></div><br>\n";
  if ($print) print $text;
  else return $text;
}

/**
 * Get google url query for geo data
 *
 * @param data_array the array of data_array
 * @return the url with data
 */
function nsp_GetGoogleGeo($data_array) {
  if(empty($data_array)) { return ''; }
  // get hash
  foreach($data_array as $key => $value ) {
    $values[] = $value;
    $labels[] = $key;
  }
  return "?cht=Country&chd=".(implode(",",$values))."&chlt=Popularity&chld=".(implode(",",$labels));
}

/**
 * Get google url query for pie data
 *
 * @param data_array the array of data_array
 * @param title the title to use
 * @return the url with data
 */
function nsp_GetGooglePie($title, $data_array) {
  if(empty($data_array)) { return ''; }
  // get hash
  foreach($data_array as $key => $value ) {
    $values[] = $value;
    $labels[] = $key;
  }

  return "?title=".$title."&chd=".(implode(",",$values))."&chl=".urlencode(implode("|",$labels));
}

?>
