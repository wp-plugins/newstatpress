<?php
/***********************************************************
Plugin Name: NewStatPress
Plugin URI: http://newstatpress.altervista.org
Description: Real time stats for your Wordpress blog
Version: 1.0.7
Author: Stefano Tognon and cHab (from Daniele Lippi works)
Author URI: http://newstatpress.altervista.org
************************************************************/

$_NEWSTATPRESS['version']='1.0.7';
$_NEWSTATPRESS['feedtype']='';

global $newstatpress_dir, $wpdb, $nsp_option_vars, $nsp_widget_vars;


define("nsp_TABLENAME", $wpdb->prefix . "statpress");
define("nsp_BASENAME", dirname(plugin_basename(__FILE__)));


$newstatpress_dir = WP_PLUGIN_DIR . '/' .nsp_BASENAME;

$nsp_option_vars=array( // list of option variable name, with default value associated
                        'overview'=>array('name'=>'newstatpress_el_overview','value'=>'10'),
                        'top_days'=>array('name'=>'newstatpress_el_top_days','value'=>'5'),
                        'os'=>array('name'=>'newstatpress_el_os','value'=>'10'),
                        'browser'=>array('name'=>'newstatpress_el_browser','value'=>'10'),
                        'feed'=>array('name'=>'newstatpress_el_feed','value'=>'5'),
                        'searchengine'=>array('name'=>'newstatpress_el_searchengine','value'=>'10'),
                        'search'=>array('name'=>'newstatpress_el_search','value'=>'20'),
                        'referrer'=>array('name'=>'newstatpress_el_referrer','value'=>'10'),
                        'languages'=>array('name'=>'newstatpress_el_languages','value'=>'20'),
                        'spiders'=>array('name'=>'newstatpress_el_spiders','value'=>'10'),
                        'pages'=>array('name'=>'newstatpress_el_pages','value'=>'5'),
                        'visitors'=>array('name'=>'newstatpress_el_visitors','value'=>'5'),
                        'daypages'=>array('name'=>'newstatpress_el_daypages','value'=>'5'),
                        'ippages'=>array('name'=>'newstatpress_el_ippages','value'=>'5'),
                        'ip_per_page_newspy'=>array('name'=>'newstatpress_ip_per_page_newspy','value'=>''),
                        'visits_per_ip_newspy'=>array('name'=>'newstatpress_visits_per_ip_newspy','value'=>''),
                        'bot_per_page_spybot'=>array('name'=>'newstatpress_bot_per_page_spybot','value'=>''),
                        'visits_per_bot_spybot'=>array('name'=>'newstatpress_visits_per_bot_spybot','value'=>''),
                        'autodelete'=>array('name'=>'newstatpress_autodelete','value'=>''),
                        'autodelete_spiders'=>array('name'=>'newstatpress_autodelete_spiders','value'=>''),
                        'daysinoverviewgraph'=>array('name'=>'newstatpress_daysinoverviewgraph','value'=>''),
                        'ignore_users'=>array('name'=>'newstatpress_ignore_users','value'=>''),
                        'ignore_ip'=>array('name'=>'newstatpress_ignore_ip','value'=>''),
                        'ignore_permalink'=>array('name'=>'newstatpress_ignore_permalink','value'=>''),
                        'updateint'=>array('name'=>'newstatpress_updateint','value'=>''),
                        'calculation'=>array('name'=>'newstatpress_calculation_method','value'=>'classic'),
                        'menu_cap'=>array('name'=>'newstatpress_mincap','value'=>'read'),
                        'menuoverview_cap'=>array('name'=>'newstatpress_menuoverview_cap','value'=>'switch_themes'),
                        'menudetails_cap'=>array('name'=>'newstatpress_menudetails_cap','value'=>'switch_themes'),
                        'menuvisits_cap'=>array('name'=>'newstatpress_menuvisits_cap','value'=>'switch_themes'),
                        'menusearch_cap'=>array('name'=>'newstatpress_menusearch_cap','value'=>'switch_themes'),
                        'menuoptions_cap'=>array('name'=>'newstatpress_menuoptions_cap','value'=>'edit_users'),
                        'menutools_cap'=>array('name'=>'newstatpress_menutools_cap','value'=>'switch_themes'),
                        'menucredits_cap'=>array('name'=>'newstatpress_menucredits_cap','value'=>'read'),
                        'apikey'=>array('name'=>'newstatpress_apikey','value'=>'read'),
                        'ip2nation'=>array('name'=>'newstatpress_ip2nation','value'=>'none')
                      );
                      // ''=>array('name'=>'','value'=>''),

$nsp_widget_vars=array( // list of widget variables name, with description associated
                       array('visits',__('Today visits', 'newstatpress')),
                       array('yvisits',__('Yesterday visits', 'newstatpress')),
                       array('mvisits',__('Month visits', 'newstatpress')),
                       array('wvisits',__('Week visits', 'newstatpress')),
                       array('totalvisits',__('Total visits', 'newstatpress')),
                       array('totalpageviews',__('Total pages view', 'newstatpress')),
                       array('todaytotalpageviews',__('Total pages view today', 'newstatpress')),
                       array('thistotalvisits',__('This page, total visits', 'newstatpress')),
                       array('alltotalvisits',__('All page, total visits', 'newstatpress')),
                       array('os',__('Visitor Operative System', 'newstatpress')),
                       array('browser',__('Visitor Browser', 'newstatpress')),
                       array('ip',__('Visitor IP address', 'newstatpress')),
                       array('since',__('Date of the first hit', 'newstatpress')),
                       array('visitorsonline',__('Counts all online visitors', 'newstatpress')),
                       array('usersonline',__('Counts logged online visitors', 'newstatpress')),
                       array('toppost',__('The most viewed Post', 'newstatpress'))
                      );

/**
 * Check to update of the plugin
 *
 *******************************/
function nsp_UpdateCheck() {

  global $_NEWSTATPRESS;

  $active_version = get_option('newstatpress_version', '0' );

  if (version_compare( $active_version, $_NEWSTATPRESS['version'], '<' )) {
    update_option('newstatpress_version', $_NEWSTATPRESS['version']);
  }
}
add_action( 'admin_init', 'nsp_UpdateCheck' );


/**
 * Load CSS style, languages files, extra files
 *
 ***********************************************/
 function nsp_RegisterPluginStyles() {

   $style_path=plugins_url('./css/style.css', __FILE__);

   wp_register_style('NewStatPressStyles', $style_path);
   wp_enqueue_style('NewStatPressStyles');

   wp_enqueue_script("jquery");

   $style_path2=plugins_url('./js/jquery.idTabs.min.js', __FILE__);

   wp_register_script('NewStatPressJs', $style_path2);
   wp_enqueue_script('NewStatPressJs');

 }
 add_action( 'admin_enqueue_scripts', 'nsp_RegisterPluginStyles' );



 function nsp_load_textdomain() {
   load_plugin_textdomain( 'newstatpress', false, nsp_BASENAME . '/langs' );
 }
 add_action( 'plugins_loaded', 'nsp_load_textdomain' );

 if (is_admin())
 {
   require ('includes/nsp_functions-extra.php');
   require ('includes/nsp_credits.php');
   require ('includes/nsp_tools.php');
   require ('includes/nsp_options.php');
   require ('includes/nsp_visits.php');
   require ('includes/nsp_details.php');
   require ('includes/nsp_search.php');
   require ('includes/nsp_dashboard.php');

   add_action('wp_dashboard_setup', 'nsp_AddDashBoardWidget' );
}


/**
 * Add pages for NewStatPress plugin
 *
 *************************************/
function nsp_BuildPluginMenu() {

  global $nsp_option_vars;
  global $current_user;
  get_currentuserinfo();

  // Fix capability if it's not defined
  // $capability=get_option('newstatpress_mincap') ;
  // if(!$capability) //default value
    $capability=$nsp_option_vars['menu_cap']['value'];

  $overview_capability=get_option('newstatpress_menuoverview_cap') ;
  if(!$overview_capability) //default value
    $overview_capability=$nsp_option_vars['menuoverview_cap']['value'];

  $details_capability=get_option('newstatpress_menudetails_cap') ;
  if(!$details_capability) //default value
    $details_capability=$nsp_option_vars['menudetails_cap']['value'];

  $visits_capability=get_option('newstatpress_menuvisits_cap') ;
  if(!$visits_capability) //default value
    $visits_capability=$nsp_option_vars['menuvisits_cap']['value'];

  $search_capability=get_option('newstatpress_menusearch_cap') ;
  if(!$search_capability) //default value
    $search_capability=$nsp_option_vars['menusearch_cap']['value'];

  $tools_capability=get_option('newstatpress_menutools_cap') ;
  if(!$tools_capability) //default value
    $tools_capability=$nsp_option_vars['menutools_cap']['value'];

  $options_capability=get_option('newstatpress_menuoptions_cap') ;
  if(!$options_capability) //default value
    $options_capability=$nsp_option_vars['menuoptions_cap']['value'];

  $credits_capability=$nsp_option_vars['menucredits_cap']['value'];

  // Display menu with personalized capabilities if user IS NOT "subscriber"
  if ( user_can( $current_user, "edit_posts" ) ) {
    add_menu_page('NewStatPres', 'NewStatPress', $capability, 'nsp-main', 'nsp_NewStatPressMain', plugins_url('newstatpress/images/stat.png',nsp_BASENAME));
    add_submenu_page('nsp-main', __('Overview','newstatpress'), __('Overview','newstatpress'), $overview_capability, 'nsp-main', 'nsp_NewStatPressMain');
    add_submenu_page('nsp-main', __('Details','newstatpress'), __('Details','newstatpress'), $details_capability, 'nsp_details', 'nsp_DisplayDetails');
    add_submenu_page('nsp-main', __('Visits','newstatpress'), __('Visits','newstatpress'), $visits_capability, 'nsp_visits', 'nsp_DisplayVisitsPage');
    add_submenu_page('nsp-main', __('Search','newstatpress'), __('Search','newstatpress'), $search_capability, 'nsp_search', 'nsp_DatabaseSearch');
    add_submenu_page('nsp-main', __('Tools','newstatpress'), __('Tools','newstatpress'), $tools_capability, 'nsp_tools', 'nsp_DisplayToolsPage');
    add_submenu_page('nsp-main', __('Options','newstatpress'), __('Options','newstatpress'), $options_capability, 'nsp_options', 'nsp_Options');
    add_submenu_page('nsp-main', __('Credits','newstatpress'), __('Credits','newstatpress'), $credits_capability, 'nsp_credits', 'nsp_DisplayCreditsPage');
  }
}
add_action('admin_menu', 'nsp_BuildPluginMenu');


/**
 * Get the url of the plugin
 *
 * @return the url of the plugin
 ********************************/
function PluginUrl() {
  //Try to use WP API if possible, introduced in WP 2.6
  if (function_exists('plugins_url')) return trailingslashit(plugins_url(basename(dirname(__FILE__))));

  //Try to find manually... can't work if wp-content was renamed or is redirected
  $path = dirname(__FILE__);
  $path = str_replace("\\","/",$path);
  $path = trailingslashit(get_bloginfo('wpurl')) . trailingslashit(substr($path,strpos($path,"wp-content/")));

  return $path;
}


/**
 * Check and Export if capability of user allow that
 *
 ***************************************************/
function nsp_checkExport() {
  if (isset($_GET['newstatpress_action']) && $_GET['newstatpress_action'] == 'exportnow') {
    $mincap=get_option('newstatpress_mincap');
    if ($mincap == '') $mincap = "level_8";
    if ( current_user_can( $mincap ) ) {
      nsp_ExportNow();
    }
  }
}
add_action('init','nsp_checkExport');


/**
 * Show overwiew
 *
 *****************/
function nsp_NewStatPressMain() {
  global $wpdb;
  $table_name = nsp_TABLENAME;

  nsp_MakeOverview('main');

  $_newstatpress_url=PluginUrl();

  // determine the structure to use for URL
  $permalink_structure = get_option('permalink_structure');
  if ($permalink_structure=='') $extra="/?";
  else $extra="/";

  $querylimit="LIMIT ".((get_option('newstatpress_el_overview')=='') ? 10:get_option('newstatpress_el_overview'));

  # Tabella Last hits
  print "<div class='wrap'><h2>". __('Last hits','newstatpress'). "</h2><table class='widefat nsp'><thead><tr><th scope='col'>". __('Date','newstatpress'). "</th><th scope='col'>". __('Time','newstatpress'). "</th><th scope='col'>IP</th><th scope='col'>". __('Country','newstatpress').'/'.__('Language','newstatpress'). "</th><th scope='col'>". __('Page','newstatpress'). "</th><th scope='col'>". __('Feed','newstatpress'). "</th><th></th><th scope='col' style='width:120px;'>". __('OS','newstatpress'). "</th><th></th><th scope='col' style='width:120px;'>". __('Browser','newstatpress'). "</th></tr></thead>";
  print "<tbody id='the-list'>";

  $fivesdrafts = $wpdb->get_results("
    SELECT *
    FROM $table_name
    WHERE (os<>'' OR feed<>'')
    ORDER bY id DESC $querylimit
  ");
  foreach ($fivesdrafts as $fivesdraft) {
    print "<tr>";
    print "<td>". nsp_hdate($fivesdraft->date) ."</td>";
    print "<td>". $fivesdraft->time ."</td>";
    print "<td>". $fivesdraft->ip ."</td>";
    print "<td>". $fivesdraft->nation ."</td>";
    print "<td>". nsp_Abbreviate(nsp_DecodeURL($fivesdraft->urlrequested),30) ."</td>";
    print "<td>". $fivesdraft->feed . "</td>";

    if($fivesdraft->os != '') {
      $img=$_newstatpress_url."/images/os/".str_replace(" ","_",strtolower($fivesdraft->os)).".png";
      print "<td class='browser'><img class='img_browser' SRC='$img'></td>";
    }
    else {
        print "<td></td>";
      }
    print "<td>".$fivesdraft->os . "</td>";

    if($fivesdraft->browser != '') {
      $img=str_replace(" ","",strtolower($fivesdraft->browser)).".png";
      print "<td><IMG class='img_browser' SRC='".$_newstatpress_url."/images/browsers/$img'></td>";
    } else {
       print "<td></td>";
    }
    print "<td>".$fivesdraft->browser."</td></tr>\n";
    print "</tr>";
  }
  print "</table></div>";


  # Last Search terms
  print "<div class='wrap'><h2>" . __('Last search terms','newstatpress') . "</h2><table class='widefat nsp'><thead><tr><th scope='col'>".__('Date','newstatpress')."</th><th scope='col'>".__('Time','newstatpress')."</th><th scope='col'>".__('Terms','newstatpress')."</th><th scope='col'>". __('Engine','newstatpress'). "</th><th scope='col'>". __('Result','newstatpress'). "</th></tr></thead>";
  print "<tbody id='the-list'>";
  $qry = $wpdb->get_results("
    SELECT date,time,referrer,urlrequested,search,searchengine
    FROM $table_name
    WHERE search<>''
    ORDER BY id DESC $querylimit
  ");
  foreach ($qry as $rk) {
    print "<tr>
            <td>".nsp_hdate($rk->date)."</td><td>".$rk->time."</td>
            <td><a href='".$rk->referrer."' target='_blank'>".$rk->search."</a></td>
            <td>".$rk->searchengine."</td><td><a href='".get_bloginfo('url').$extra.$rk->urlrequested."' target='_blank'>". __('page viewed','newstatpress'). "</a></td>
          </tr>\n";
  }
  print "</table></div>";

  # Referrer
  print "<div class='wrap'><h2>".__('Last referrers','newstatpress')."</h2><table class='widefat nsp'><thead><tr><th scope='col'>".__('Date','newstatpress')."</th><th scope='col'>".__('Time','newstatpress')."</th><th scope='col'>".__('URL','newstatpress')."</th><th scope='col'>".__('Result','newstatpress')."</th></tr></thead>";
  print "<tbody id='the-list'>";
  $qry = $wpdb->get_results("
    SELECT date,time,referrer,urlrequested
    FROM $table_name
    WHERE
     ((referrer NOT LIKE '".get_option('home')."%') AND
      (referrer <>'') AND
      (searchengine='')
     ) ORDER BY id DESC $querylimit
  ");
  foreach ($qry as $rk) {
    print "<tr><td>".nsp_hdate($rk->date)."</td><td>".$rk->time."</td><td><a href='".$rk->referrer."' target='_blank'>".nsp_Abbreviate($rk->referrer,80)."</a></td><td><a href='".get_bloginfo('url').$extra.$rk->urlrequested."'  target='_blank'>". __('page viewed','newstatpress'). "</a></td></tr>\n";
  }
  print "</table></div>";


  # Last Agents
  print "<div class='wrap'><h2>".__('Last agents','newstatpress')."</h2><table class='widefat nsp'><thead><tr><th scope='col'>".__('Agent','newstatpress')."</th><th scope='col'></th><th scope='col' style='width:120px;'>". __('OS','newstatpress'). "</th><th scope='col'></th><th scope='col' style='width:120px;'>". __('Browser','newstatpress').'/'. __('Spider','newstatpress'). "</th></tr></thead>";
  print "<tbody id='the-list'>";
  $qry = $wpdb->get_results("
    SELECT agent,os,browser,spider
    FROM $table_name
    GROUP BY agent,os,browser,spider
    ORDER BY id DESC $querylimit
  ");
  foreach ($qry as $rk) {
    print "<tr><td>".$rk->agent."</td>";
    if($rk->os != '') {
      $img=str_replace(" ","_",strtolower($rk->os)).".png";
      print "<td><IMG class='img_browser' SRC='".$_newstatpress_url."/images/os/$img'> </td>";
    } else {
        print "<td></td>";
      }
    print "<td>". $rk->os . "</td>";
    if($rk->browser != '') {
      $img=str_replace(" ","",strtolower($rk->browser)).".png";
      print "<td><IMG class='img_browser' SRC='".$_newstatpress_url."/images/browsers/$img'></td>";
    } else {
        print "<td></td>";
      }
    print "<td>".$rk->browser." ".$rk->spider."</td></tr>\n";
  }
  print "</table></div>";


  # Last pages
  print "<div class='wrap'><h2>".__('Last pages','newstatpress')."</h2><table class='widefat nsp'><thead><tr><th scope='col'>".__('Date','newstatpress')."</th><th scope='col'>".__('Time','newstatpress')."</th><th scope='col'>".__('Page','newstatpress')."</th><th scope='col' style='width:17px;'></th><th scope='col' style='width:120px;'>".__('OS','newstatpress')."</th><th style='width:17px;'></th><th scope='col' style='width:120px;'>".__('Browser','newstatpress')."</th></tr></thead>";
  print "<tbody id='the-list'>";
  $qry = $wpdb->get_results("
    SELECT date,time,urlrequested,os,browser,spider
    FROM $table_name
    WHERE (spider='' AND feed='')
    ORDER BY id DESC $querylimit
  ");
  foreach ($qry as $rk) {
    print "<tr><td>".nsp_hdate($rk->date)."</td><td>".$rk->time."</td><td>".nsp_Abbreviate(nsp_DecodeURL($rk->urlrequested),60)."</td>";
    if($rk->os != '') {
      $img=str_replace(" ","_",strtolower($rk->os)).".png";
      print "<td><IMG class='img_browser' SRC='".$_newstatpress_url."/images/os/$img'> </td>";
    } else {
        print "<td></td>";
      }
    print "<td>". $rk->os . "</td>";
    if($rk->browser != '') {
      $img=str_replace(" ","",strtolower($rk->browser)).".png";
      print "<td><IMG class='img_browser' SRC='".$_newstatpress_url."/images/browsers/$img'></td>";
    } else {
        print "<td></td>";
      }
    print "<td>".$rk->browser." ".$rk->spider."</td></tr>\n";
  }
  print "</table></div>";


  # Last Spiders
  print "<div class='wrap'><h2>".__('Last spiders','newstatpress')."</h2><table class='widefat nsp'><thead><tr><th scope='col'>".__('Date','newstatpress')."</th><th scope='col'>".__('Time','newstatpress')."</th><th scope='col'></th><th scope='col'>".__('Spider','newstatpress')."</th><th scope='col'>".__('Agent','newstatpress')."</th></tr></thead>";
  print "<tbody id='the-list'>";
  $qry = $wpdb->get_results("
    SELECT date,time,agent,os,browser,spider
    FROM $table_name
    WHERE (spider<>'')
    ORDER BY id DESC $querylimit
  ");
  foreach ($qry as $rk) {
    print "<tr><td>".nsp_hdate($rk->date)."</td><td>".$rk->time."</td>";
    if($rk->spider != '') {
      $img=str_replace(" ","_",strtolower($rk->spider)).".png";
      print "<td><IMG class='img_os' SRC='".$_newstatpress_url."/images/spider/$img'> </td>";
    } else print "<td></td>";
    print "<td>".$rk->spider."</td><td> ".$rk->agent."</td></tr>\n";
  }
  print "</table></div>";

  print "<br />";
  print "&nbsp;<i>StatPress table size: <b>".nsp_TableSize(nsp_TABLENAME)."</b></i><br />";
  print "&nbsp;<i>StatPress current time: <b>".current_time('mysql')."</b></i><br />";
  print "&nbsp;<i>RSS2 url: <b>".get_bloginfo('rss2_url').' ('.nsp_ExtractFeedFromUrl(get_bloginfo('rss2_url')).")</b></i><br />";
}

/**
 * Extract the feed from the given url
 *
 * @param url the url to parse
 * @return the extracted url
 *************************************/
function nsp_ExtractFeedFromUrl($url) {
  list($null,$q)=explode("?",$url);
  if (strpos($q, "&")!== false) list($res,$null)=explode("&",$q);
  else $res=$q;
  return $res;
}


/**
 * Decode the url in a better manner
 *
 * @param out_url
 * @return url decoded
 ************************************/
function newstatpress_Decode($out_url) {
  if(!nsp_PermalinksEnabled()) {
    if ($out_url == '') $out_url = __('Page', 'newstatpress') . ": Home";
    if (my_substr($out_url, 0, 4) == "cat=") $out_url = __('Category', 'newstatpress') . ": " . get_cat_name(my_substr($out_url, 4));
    if (my_substr($out_url, 0, 2) == "m=") $out_url = __('Calendar', 'newstatpress') . ": " . my_substr($out_url, 6, 2) . "/" . my_substr($out_url, 2, 4);
    if (my_substr($out_url, 0, 2) == "s=") $out_url = __('Search', 'newstatpress') . ": " . my_substr($out_url, 2);
    if (my_substr($out_url, 0, 2) == "p=") {
      $subOut=my_substr($out_url, 2);
      $post_id_7 = get_post($subOut, ARRAY_A);
      $out_url = $post_id_7['post_title'];
    }
    if (my_substr($out_url, 0, 8) == "page_id=") {
      $subOut=my_substr($out_url, 8);
      $post_id_7 = get_page($subOut, ARRAY_A);
      $out_url = __('Page', 'newstatpress') . ": " . $post_id_7['post_title'];
    }
 } else {
     if ($out_url == '') $out_url = __('Page', 'newstatpress') . ": Home";
     else if (my_substr($out_url, 0, 9) == "category/") $out_url = __('Category', 'newstatpress') . ": " . get_cat_name(my_substr($out_url, 9));
          else if (my_substr($out_url, 0, 2) == "s=") $out_url = __('Search', 'newstatpress') . ": " . my_substr($out_url, 2);
               else if (my_substr($out_url, 0, 2) == "p=") {
                      // not working yet
                      $subOut=my_substr($out_url, 2);
                      $post_id_7 = get_post($subOut, ARRAY_A);
                      $out_url = $post_id_7['post_title'];
                    } else if (my_substr($out_url, 0, 8) == "page_id=") {
                             // not working yet
                             $subOut=my_substr($out_url, 8);
                             $post_id_7 = get_page($subOut, ARRAY_A);
                             $out_url = __('Page', 'newstatpress') . ": " . $post_id_7['post_title'];
                           }
   }
   return $out_url;
}


/**
 * Get true if permalink is enabled in Wordpress
 * (taken in statpress-visitors)
 *
 * @return true if permalink is enabled in Wordpress
 ***************************************************/
function nsp_PermalinksEnabled() {
  global $wpdb;

  $result = $wpdb->get_row('SELECT `option_value` FROM `' . $wpdb->prefix . 'options` WHERE `option_name` = "permalink_structure"');
  if ($result->option_value != '') return true;
  else return false;
}


/**
 * PHP 4 compatible mb_substr function
 * (taken in statpress-visitors)
 */
function my_substr($str, $x, $y = 0) {
  if($y == 0) $y = strlen($str) - $x;
  if(function_exists('mb_substr'))
  return mb_substr($str, $x, $y);
  else
 return substr($str, $x, $y);
}


/**
 * Get page period taken in statpress-visitors
 */
function newstatpress_page_periode() {
  // pp is the display page periode
  if(isset($_GET['pp'])) {
    // Get Current page periode from URL
    $periode = $_GET['pp'];
    if($periode <= 0)
      // Periode is less than 0 then set it to 1
      $periode = 1;
  } else
      // URL does not show the page set it to 1
      $periode = 1;
  return $periode;
}

/**
 * Get page post taken in statpress-visitors
 *
 * @return page
 ******************************************/
function newstatpress_page_posts() {
  global $wpdb;
  // pa is the display pages Articles
  if(isset($_GET['pa'])) {
    // Get Current page Articles from URL
    $pageA = $_GET['pa'];
    if($pageA <= 0)
      // Article is less than 0 then set it to 1
      $pageA = 1;
  } else
      // URL does not show the Article set it to 1
      $pageA = 1;
  return $pageA;
}

// Not use!!! commented by chab
/**
 * Check if the argument is an IP addresses
 *
 * @param ip the ip to check
 * @return TRUE if it is an ip
 */
// function nsp_CheckIP($ip) {
//   return ( ! preg_match( "/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $ip)) ? FALSE : TRUE;
// }

/**
 * Abbreviate the given string to a fixed length
 *
 * @param s the string
 * @param c the number of chars
 * @return the abbreviate string
 ***********************************************/
function nsp_Abbreviate($s,$c) {
  $s=__($s);
  $res=""; if(strlen($s)>$c) { $res="..."; }
  return substr($s,0,$c).$res;
}


/**
 * Decode the given url
 *
 * @param out_url the given url to decode
 * @return the decoded url
 ****************************************/
function nsp_DecodeURL($out_url) {
  if($out_url == '') { $out_url=__('Page','newstatpress').": Home"; }
  if(substr($out_url,0,4)=="cat=") { $out_url=__('Category','newstatpress').": ".get_cat_name(substr($out_url,4)); }
  if(substr($out_url,0,2)=="m=") { $out_url=__('Calendar','newstatpress').": ".substr($out_url,6,2)."/".substr($out_url,2,4); }
  if(substr($out_url,0,2)=="s=") { $out_url=__('Search','newstatpress').": ".substr($out_url,2); }
  if(substr($out_url,0,2)=="p=") {
    $subOut=substr($out_url,2);
    $post_id_7 = get_post($subOut, ARRAY_A);
    $out_url = $post_id_7['post_title'];
  }
  if(substr($out_url,0,8)=="page_id=") {
    $subOut=substr($out_url,8);
    $post_id_7=get_page($subOut, ARRAY_A);
    $out_url = __('Page','newstatpress').": ".$post_id_7['post_title'];
  }
  return $out_url;
}


function nsp_URL() {
  $urlRequested = (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '' );
  if ( $urlRequested == "" ) { // SEO problem!
    $urlRequested = (isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : '' );
  }
  if(substr($urlRequested,0,2) == '/?') { $urlRequested=substr($urlRequested,2); }
  if($urlRequested == '/') { $urlRequested=''; }
  return $urlRequested;
}


/**
 * Convert data us to default format di Wordpress
 *
 * @param dt: date to convert
 * @return converted data
 ****************************************************/
function nsp_hdate($dt = "00000000") {
  return mysql2date(get_option('date_format'), substr($dt,0,4)."-".substr($dt,4,2)."-".substr($dt,6,2));
}



function newstatpress_hdate($dt = "00000000") {
  return mysql2date(get_option('date_format'), my_substr($dt, 0, 4) . "-" . my_substr($dt, 4, 2) . "-" . my_substr($dt, 6, 2));
}


function nsp_TableSize($table) {
  global $wpdb;
  $res = $wpdb->get_results("SHOW TABLE STATUS LIKE '$table'");
  foreach ($res as $fstatus) {
    $data_lenght = $fstatus->Data_length;
    $data_rows = $fstatus->Rows;
  }
  return number_format(($data_lenght/1024/1024), 2, ",", " ")." Mb ($data_rows ". __('records','newstatpress').")";
}


function nsp_IndexTableSize($table) {
  global $wpdb;
  $res = $wpdb->get_results("SHOW TABLE STATUS LIKE '$table'");
  foreach ($res as $fstatus) {
    $index_lenght = $fstatus->Index_length;
  }
  return number_format(($index_lenght/1024/1024), 2, ",", " ")." Mb";
}


function nsp_GetLanguage($accepted) {
  return substr($accepted,0,2);
}


function nsp_GetQueryPairs($url){
  $parsed_url = parse_url($url);
  $tab=parse_url($url);
  $host = $tab['host'];
  if(key_exists("query",$tab)){
    $query=$tab["query"];
    return explode("&",$query);
  } else {return null;}
}


/**
 * Get OS from the given argument
 *
 * @param arg the argument to parse for OS
 * @return the OS find in configuration file
 *******************************************/
function nsp_GetOs($arg) {
  global $newstatpress_dir;

  $arg=str_replace(" ","",$arg);
  $lines = file($newstatpress_dir.'/def/os.dat');
  foreach($lines as $line_num => $os) {
    list($nome_os,$id_os)=explode("|",$os);
    if(strpos($arg,$id_os)===FALSE) continue;
    return $nome_os;     // fount
  }
  return '';
}

/**
 * Get Browser from the given argument
 *
 * @param arg the argument to parse for Brower
 * @return the Browser find in configuration file
 ************************************************/
function nsp_GetBrowser($arg) {
  global $newstatpress_dir;

  $arg=str_replace(" ","",$arg);
  $lines = file($newstatpress_dir.'/def/browser.dat');
  foreach($lines as $line_num => $browser) {
    list($nome,$id)=explode("|",$browser);
    if(strpos($arg,$id)===FALSE) continue;
    return $nome;     // fount
  }
  return '';
}

/**
 * Check if the given ip is to ban
 *
 * @param arg the ip to check
 * @return '' id the address is banned
 */
function nsp_CheckBanIP($arg){
  global $newstatpress_dir;

  $lines = file($newstatpress_dir.'/def/banips.dat');
  foreach($lines as $line_num => $banip) {
    if(strpos($arg,rtrim($banip,"\n"))===FALSE) continue;
    return ''; // this is banned
  }
  return $arg;
}

/**
 * Get the search engines
 *
 * @param refferer the url to test
 * @return the search engine present in the url
 */
function nsp_GetSE($referrer = null){
  global $newstatpress_dir;

  $key = null;
  $lines = file($newstatpress_dir.'/def/searchengines.dat');
  foreach($lines as $line_num => $se) {
    list($nome,$url,$key)=explode("|",$se);
    if(strpos($referrer,$url)===FALSE) continue;

    # find if
    $variables = nsp_GetQueryPairs(html_entity_decode($referrer));
    $i = count($variables);
    while($i--){
      $tab=explode("=",$variables[$i]);
      if($tab[0] == $key){return ($nome."|".urldecode($tab[1]));}
    }
  }
  return null;
}

/**
 * Get the spider from the given agent
 *
 * @param agent the agent string
 * @return agent the fount agent
 *************************************/
function nsp_GetSpider($agent = null){
  global $newstatpress_dir;

  $agent=str_replace(" ","",$agent);
  $key = null;
  $lines = file($newstatpress_dir.'/def/spider.dat');
  foreach($lines as $line_num => $spider) {
    list($nome,$key)=explode("|",$spider);
    if(strpos($agent,$key)===FALSE) continue;
    # fount
    return $nome;
  }
  return null;
}

/**
 * Get the previous month in 'YYYYMM' format
 *
 * @return the previous month
 */
function nsp_Lastmonth() {
  $ta = getdate(current_time('timestamp'));

  $year = $ta['year'];
  $month = $ta['mon'];

  --$month; // go back 1 month

  if( $month === 0 ): // if this month is Jan
    --$year; // go back a year
    $month = 12; // last month is Dec
  endif;

  // return in format 'YYYYMM'
  return sprintf( $year.'%02d', $month);
}

/**
 * Create or update the table
 *
 * @param action to do: update, create
 *************************************/
 function nsp_BuildPluginSQLTable($action) {

   global $wpdb;
   global $wp_db_version;
   $table_name = nsp_TABLENAME;
   $charset_collate = $wpdb->get_charset_collate();
   $index_list=array(array('Key_name'=>"spider_nation", 'Column_name'=>"(spider, nation)"),
                     array('Key_name'=>"ip_date", 'Column_name'=>"(ip, date)"),
                     array('Key_name'=>"agent", 'Column_name'=>"(agent)"),
                     array('Key_name'=>"search", 'Column_name'=>"(search)"),
                     array('Key_name'=>"referrer", 'Column_name'=>"(referrer)"),
                     array('Key_name'=>"feed_spider_os", 'Column_name'=>"(feed, spider, os)"),
                     array('Key_name'=>"os", 'Column_name'=>"(os)"),
                     array('Key_name'=>"date_feed_spider", 'Column_name'=>"(date, feed, spider)"),
                     array('Key_name'=>"feed_spider_browser", 'Column_name'=>"(feed, spider, browser)"),
                     array('Key_name'=>"browser", 'Column_name'=>"(browser)")
                     );
   // Add by chab
   // IF the table is already created then DROP INDEX for update
   if ($action=='')
     $action='create';

   $sql_createtable = "
    CREATE TABLE ". $table_name . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      date int(8),
      time time,
      ip varchar(39),
      urlrequested varchar(250),
      agent varchar(250),
      referrer varchar(512),
      search varchar(250),
      nation varchar(2),
      os varchar(30),
      browser varchar(32),
      searchengine varchar(16),
      spider varchar(32),
      feed varchar(8),
      user varchar(16),
      timestamp timestamp DEFAULT 0,
      UNIQUE KEY id (id)";

   if ($action=='create') {
     foreach ($index_list as $index)
     {
       $Key_name=$index['Key_name'];
       $Column_name=$index['Column_name'];
       $sql_createtable.=", INDEX $Key_name $Column_name";
     }
   }
   elseif ($action=='update') {
       foreach ($index_list as $index)
       {
         $Key_name=$index['Key_name'];
         $Column_name=$index['Column_name'];
         if ($wpdb->query("SHOW INDEXES FROM $table_name WHERE Key_name ='$Key_name'")=='') {
           $sql_createtable.=",\n INDEX $Key_name $Column_name";
         }
       }
   }
   $sql_createtable.=") $charset_collate;";


  //  echo $sql_createtable;

  if($wp_db_version >= 5540) $page = 'wp-admin/includes/upgrade.php';
  else $page = 'wp-admin/upgrade'.'-functions.php';

  require_once(ABSPATH . $page);
  dbDelta($sql_createtable);
}

/**
 * Get if this is a feed
 *
 * @param url the url to test
 * @return the kind of feed that is found
 *****************************************/
function nsp_IsFeed($url) {
  if (stristr($url,get_bloginfo('rdf_url')) != FALSE) { return 'RDF'; }
  if (stristr($url,get_bloginfo('rss2_url')) != FALSE) { return 'RSS2'; }
  if (stristr($url,get_bloginfo('rss_url')) != FALSE) { return 'RSS'; }
  if (stristr($url,get_bloginfo('atom_url')) != FALSE) { return 'ATOM'; }
  if (stristr($url,get_bloginfo('comments_rss2_url')) != FALSE) { return 'COMMENT'; }
  if (stristr($url,get_bloginfo('comments_atom_url')) != FALSE) { return 'COMMENT'; }
  if (stristr($url,'wp-feed.php') != FALSE) { return 'RSS2'; }
  if (stristr($url,'/feed/') != FALSE) { return 'RSS2'; }
  return '';
}

/**
 * Insert statistic into the database
 *
 ************************************/
function nsp_StatAppend() {

  global $wpdb;
  $table_name = nsp_TABLENAME;
  global $userdata;
  global $_STATPRESS;

  get_currentuserinfo();
  $feed='';

  // Time
  $timestamp  = current_time('timestamp');
  $vdate  = gmdate("Ymd",$timestamp);
  $vtime  = gmdate("H:i:s",$timestamp);
  $timestamp = date('Y-m-d H:i:s', $timestamp);

  // IP
  $ipAddress = $_SERVER['REMOTE_ADDR'];

  // Is this IP blacklisted from file?
  if(nsp_CheckBanIP($ipAddress) == '') { return ''; }

  // Is this IP blacklisted from user?
  $to_ignore = get_option('newstatpress_ignore_ip', array());
  foreach($to_ignore as $a_ip_range){
    list ($ip_to_ignore, $mask) = @explode("/", trim($a_ip_range));
    if (empty($mask)) $mask = 32;
    $long_ip_to_ignore = ip2long($ip_to_ignore);
    $long_mask = bindec( str_pad('', $mask, '1') . str_pad('', 32-$mask, '0') );
    $long_masked_user_ip = ip2long($ipAddress) & $long_mask;
    $long_masked_ip_to_ignore = $long_ip_to_ignore & $long_mask;
    if ($long_masked_user_ip == $long_masked_ip_to_ignore) { return ''; }
  }

  if(get_option('newstatpress_cryptip')=='checked') {
    $ipAddress = crypt($ipAddress,'newstatpress');
  }

  // URL (requested)
  $urlRequested=nsp_URL();
  if (preg_match("/.ico$/i", $urlRequested)) { return ''; }
  if (preg_match("/favicon.ico/i", $urlRequested)) { return ''; }
  if (preg_match("/.css$/i", $urlRequested)) { return ''; }
  if (preg_match("/.js$/i", $urlRequested)) { return ''; }
  if (stristr($urlRequested,"/wp-content/plugins") != FALSE) { return ''; }
  if (stristr($urlRequested,"/wp-content/themes") != FALSE) { return ''; }
  if (stristr($urlRequested,"/wp-admin/") != FALSE) { return ''; }
  $urlRequested=esc_sql($urlRequested);

  // Is a given permalink blacklisted?
  $to_ignore = get_option('newstatpress_ignore_permalink', array());
    foreach($to_ignore as $a_filter){
    if (!empty($urlRequested) && strpos($urlRequested, $a_filter) === 0) { return ''; }
  }

  $referrer = (isset($_SERVER['HTTP_REFERER']) ? htmlentities($_SERVER['HTTP_REFERER']) : '');
  $referrer=esc_sql($referrer);
  $referrer=esc_html($referrer);

  $userAgent = (isset($_SERVER['HTTP_USER_AGENT']) ? htmlentities($_SERVER['HTTP_USER_AGENT']) : '');
  $userAgent=esc_sql($userAgent);
  $userAgent=esc_html($userAgent);

  $spider=nsp_GetSpider($userAgent);

  if(($spider != '') and (get_option('newstatpress_donotcollectspider')=='checked')) { return ''; }

  if($spider != '') {
    $os=''; $browser='';
  } else {
      // Trap feeds
      $feed=nsp_IsFeed(get_bloginfo('url').$_SERVER['REQUEST_URI']);
      // Get OS and browser
      $os=nsp_GetOs($userAgent);
      $browser=nsp_GetBrowser($userAgent);

     $exp_referrer=nsp_GetSE($referrer);
     if (isset($exp_referrer)) {
      list($searchengine,$search_phrase)=explode("|",$exp_referrer);
     } else {
         $searchengine='';
         $search_phrase='';
       }
    }

  // Country (ip2nation table) or language
  $countrylang="";
  if($wpdb->get_var("SHOW TABLES LIKE 'ip2nation'") == 'ip2nation') {
    $sql='SELECT *
          FROM ip2nation
          WHERE ip < INET_ATON("'.$ipAddress.'")
          ORDER BY ip DESC
          LIMIT 0,1';
    $qry = $wpdb->get_row($sql);
    $countrylang=$qry->country;
  }

  if($countrylang == '') {
    $countrylang=nsp_GetLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE']);
  }

  // Auto-delete visits if...
  if(get_option('newstatpress_autodelete') != '') {
    $int = filter_var(get_option('newstatpress_autodelete'), FILTER_SANITIZE_NUMBER_INT);
    # secure action
    if ($int>=1) {
      $t=gmdate('Ymd', current_time('timestamp')-86400*$int*30);

      $results =$wpdb->query( "DELETE FROM " . $table_name . " WHERE date < '" . $t . "'");
    }
  }

  // Auto-delete spiders visits if...
  if(get_option('newstatpress_autodelete_spiders') != '') {
    $int = filter_var(get_option('newstatpress_autodelete_spiders'), FILTER_SANITIZE_NUMBER_INT);

    # secure action
    if ($int>=1) {
      $t=gmdate('Ymd', current_time('timestamp')-86400*$int*30);

      $results =$wpdb->query(
         "DELETE FROM " . $table_name . "
          WHERE date < '" . $t . "' and
                feed='' and
                spider<>''
         ");
    }
  }

  if ((!is_user_logged_in()) OR (get_option('newstatpress_collectloggeduser')=='checked')) {
    if (is_user_logged_in() AND (get_option('newstatpress_collectloggeduser')=='checked')) {
      $current_user = wp_get_current_user();

      // Is a given name to ignore?
      $to_ignore = get_option('newstatpress_ignore_users', array());
      foreach($to_ignore as $a_filter) {
        if ($current_user->user_login == $a_filter) { return ''; }
      }
    }

    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      nsp_BuildPluginSQLTable();
    }

    $login = $userdata ? $userdata->user_login : null;

    $insert =
      "INSERT INTO " . $table_name . "(
        date,
        time,
        ip,
        urlrequested,
        agent,
        referrer,
        search,
        nation,
        os,
        browser,
        searchengine,
        spider,
        feed,
        user,
        timestamp
       ) VALUES (
        '$vdate',
        '$vtime',
        '$ipAddress',
        '$urlRequested',
        '".addslashes(strip_tags($userAgent))."',
        '$referrer','" .
        addslashes(strip_tags($search_phrase))."',
        '".$countrylang."',
        '$os',
        '$browser',
        '$searchengine',
        '$spider',
        '$feed',
        '$login',
        '$timestamp'
       )";
    $results = $wpdb->query( $insert );
  }
}
add_action('send_headers', 'nsp_StatAppend');

/**
 * Generate the Ajax code for the given variable
 *
 * @param var variable to get
 * @param limit optional limit value for query
 * @param flag optional flag value for checked
 * @param url optional url address
 ************************************************/
function nsp_generateAjaxVar($var, $limit=0, $flag='', $url='') {
  global $newstatpress_dir;

  $res = "<span id=\"".$var."\">_</span>
          <script type=\"text/javascript\">

            var xmlhttp_".$var." = new XMLHttpRequest();

            xmlhttp_".$var.".onreadystatechange = function() {
              if (xmlhttp_".$var.".readyState == 4 && xmlhttp_".$var.".status == 200) {
                document.getElementById(\"".$var."\").innerHTML=xmlhttp_".$var.".responseText;
              }
            }

            var url=\"".plugins_url('newstatpress')."/includes/api/variables.php?VAR=".$var."&LIMIT=".$limit."&FLAG=".$flag."&URL=".$url."\";

            xmlhttp_".$var.".open(\"GET\", url, true);
            xmlhttp_".$var.".send();
          </script>
         ";
  return $res;
}

/**
 * Return the expanded vars into the give code. API to use for users.
 */
function NewStatPress_Print($body='') {
  return nsp_ExpandVarsInsideCode($body);
}


/**
 * Expand vars into the give code
 *
 * @param boby the code where to look for variables to expand
 * @return the modified code
 ************************************************************/
function nsp_ExpandVarsInsideCode($body) {
  global $wpdb;
  $table_name = nsp_TABLENAME;

  $vars_list=array('visits',
                   'yvisits',
                   'mvisits',
                   'wvisits',
                   'totalvisits',
                   'totalpageviews',
                   'todaytotalpageviews',
                   'alltotalvisits'
                  );

  # look for $vars_list
  foreach($vars_list as $var) {
    if(strpos(strtolower($body),"%$var%") !== FALSE) {
      $body = str_replace("%$var%", nsp_GenerateAjaxVar($var), $body);
    }
  }

  # look for %thistotalvisits%
  if(strpos(strtolower($body),"%thistotalvisits%") !== FALSE) {
    $body = str_replace("%thistotalvisits%", nsp_GenerateAjaxVar("thistotalvisits", 0, '', nsp_URL()), $body);
  }

  # look for %since%
  if(strpos(strtolower($body),"%since%") !== FALSE) {
    $qry = $wpdb->get_results(
      "SELECT date
       FROM $table_name
       ORDER BY date
       LIMIT 1;
      ");
    $body = str_replace("%since%", nsp_hdate($qry[0]->date), $body);
  }

  # look for %os%
  if(strpos(strtolower($body),"%os%") !== FALSE) {
    $userAgent = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
    $os=nsp_GetOs($userAgent);
    $body = str_replace("%os%", $os, $body);
  }

  # look for %browser%
  if(strpos(strtolower($body),"%browser%") !== FALSE) {
    $browser=nsp_GetBrowser($userAgent);
    $body = str_replace("%browser%", $browser, $body);
  }

  # look for %ip%
  if(strpos(strtolower($body),"%ip%") !== FALSE) {
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $body = str_replace("%ip%", $ipAddress, $body);
  }

  # look for %visitorsonline%
  if(strpos(strtolower($body),"%visitorsonline%") !== FALSE) {
    $act_time = current_time('timestamp');
    $from_time = date('Y-m-d H:i:s', strtotime('-4 minutes', $act_time));
    $to_time = date('Y-m-d H:i:s', $act_time);
    $qry = $wpdb->get_results(
      "SELECT count(DISTINCT(ip)) AS visitors
       FROM $table_name
       WHERE
         spider='' AND
         feed='' AND
         date = '".gmdate("Ymd", $act_time)."' AND
         timestamp BETWEEN '$from_time' AND '$to_time';
      ");
    $body = str_replace("%visitorsonline%", $qry[0]->visitors, $body);
  }

  # look for %usersonline%
  if(strpos(strtolower($body),"%usersonline%") !== FALSE) {
    $act_time = current_time('timestamp');
    $from_time = date('Y-m-d H:i:s', strtotime('-4 minutes', $act_time));
    $to_time = date('Y-m-d H:i:s', $act_time);
    $qry = $wpdb->get_results(
      "SELECT count(DISTINCT(ip)) AS users
       FROM $table_name
       WHERE
         spider='' AND
         feed='' AND
         date = '".gmdate("Ymd", $act_time)."' AND
         user<>'' AND
         timestamp BETWEEN '$from_time' AND '$to_time';
      ");
    $body = str_replace("%usersonline%", $qry[0]->users, $body);
  }

  # look for %toppost%
  if(strpos(strtolower($body),"%toppost%") !== FALSE) {
    $qry = $wpdb->get_results(
      "SELECT urlrequested,count(*) AS totale
       FROM $table_name
       WHERE
         spider='' AND
         feed='' AND
         urlrequested LIKE '%p=%'
       GROUP BY urlrequested
       ORDER BY totale DESC
       LIMIT 1;
      ");
    $body = str_replace("%toppost%", nsp_DecodeURL($qry[0]->urlrequested), $body);
  }

  # look for %topbrowser%
  if(strpos(strtolower($body),"%topbrowser%") !== FALSE) {
    $qry = $wpdb->get_results(
       "SELECT browser,count(*) AS totale
        FROM $table_name
        WHERE
          spider='' AND
          feed=''
        GROUP BY browser
        ORDER BY totale DESC
        LIMIT 1;
       ");
    $body = str_replace("%topbrowser%", nsp_DecodeURL($qry[0]->browser), $body);
  }

  # look for %topos%
  if(strpos(strtolower($body),"%topos%") !== FALSE) {
    $qry = $wpdb->get_results(
      "SELECT os,count(*) AS totale
       FROM $table_name
       WHERE
         spider='' AND
         feed=''
       GROUP BY os
       ORDER BY totale DESC
       LIMIT 1;
      ");
    $body = str_replace("%topos%", nsp_DecodeURL($qry[0]->os), $body);
  }

  # look for %topsearch%
  if(strpos(strtolower($body),"%topsearch%") !== FALSE) {
    $qry = $wpdb->get_results(
      "SELECT search, count(*) AS csearch
       FROM $table_name
       WHERE
         search<>''
       GROUP BY search
       ORDER BY csearch DESC
       LIMIT 1;
      ");
    $body = str_replace("%topsearch%", nsp_DecodeURL($qry[0]->search), $body);
  }

  return $body;
}

// TODO : if working, move the contents into the caller instead of this function
/**
 * Get top posts
 *
 * @param limit: the number of post to show
 * @param showcounts: if checked show totals
 * @return result of extraction
 *******************************************/
function nsp_TopPosts($limit=5, $showcounts='checked') {
  return nsp_GenerateAjaxVar("widget_topposts", $limit, $showcounts);
}


/**
 * Build NewsStatPress Widgets: Stat and TopPosts
 *
 ************************************************/
function nsp_WidgetInit($args) {
  if ( !function_exists('wp_register_sidebar_widget') || !function_exists('wp_register_widget_control') ) return;

  // Statistics Widget control
  function nsp_WidgetStats_control() {
    global $nsp_widget_vars;
    $options = get_option('widget_newstatpress');
    if ( !is_array($options) ) $options = array('title'=>'NewStatPress Stats', 'body'=>'Visits today: %visits%');
    if ( isset($_POST['newstatpress-submit']) && $_POST['newstatpress-submit'] ) {
      $options['title'] = strip_tags(stripslashes($_POST['newstatpress-title']));
      $options['body'] = stripslashes($_POST['newstatpress-body']);
      update_option('widget_newstatpress', $options);
    }
    $title = htmlspecialchars($options['title'], ENT_QUOTES);
    $body = htmlspecialchars($options['body'], ENT_QUOTES);

     // the form
    echo "<p>
            <label for='newstatpress-title'>". __('Title:', 'newstatpress') ."</label>
            <input class='widget-title' id='newstatpress-title' name='newstatpress-title' type='text' value=$title />
          </p>
          <p>
            <label for='newstatpress-body'>". _e('Body:', 'newstatpress') ."</label>
            <textarea class='widget-body' id='newstatpress-body' name='newstatpress-body' type='textarea' placeholder='Example: Month visits: %mvisits%...'>$body</textarea>
          </p>
          <input type='hidden' id='newstatpress-submit' name='newstatpress-submit' value='1' />
          <p>". __('Stats available: ', 'newstatpress') ."<br/ >
          <span class='widget_varslist'>";
          foreach($nsp_widget_vars as $var) {
              echo "<a href='#'>%$var[0]%  <span>"; _e($var[1], 'newstatpress'); echo "</span></a> | ";
          }
    echo "</span></p>";
  }

  function nsp_WidgetStats($args) {
    extract($args);
    $options = get_option('widget_newstatpress');
    $title = $options['title'];
    $body = $options['body'];
    echo $before_widget;
    print($before_title . $title . $after_title);
    print nsp_ExpandVarsInsideCode($body);
    echo $after_widget;
  }
  wp_register_sidebar_widget('NewStatPress', 'NewStatPress Stats', 'nsp_WidgetStats');
  wp_register_widget_control('NewStatPress', array('NewStatPress','widgets'), 'nsp_WidgetStats_control', 300, 210);

  // Top posts Widget control
  function nsp_WidgetTopPosts_control() {
    $options = get_option('widget_newstatpresstopposts');
    if ( !is_array($options) ) {
      $options = array('title'=>'NewStatPress TopPosts', 'howmany'=>'5', 'showcounts'=>'checked');
    }
    if ( isset($_POST['newstatpresstopposts-submit']) && $_POST['newstatpresstopposts-submit'] ) {
      $options['title'] = strip_tags(stripslashes($_POST['newstatpresstopposts-title']));
      $options['howmany'] = stripslashes($_POST['newstatpresstopposts-howmany']);
      $options['showcounts'] = stripslashes($_POST['newstatpresstopposts-showcounts']);
      if($options['showcounts'] == "1") {
        $options['showcounts']='checked';
      }
      update_option('widget_newstatpresstopposts', $options);
    }
    $title = htmlspecialchars($options['title'], ENT_QUOTES);
    $howmany = htmlspecialchars($options['howmany'], ENT_QUOTES);
    $showcounts = htmlspecialchars($options['showcounts'], ENT_QUOTES);
    // the form
    echo "<p style='text-align:right;'>
            <label for='newstatpresstopposts-title'>". __('Title','newstatpress') . "
            <input style='width: 250px;' id='newstatpress-title' name='newstatpresstopposts-title' type='text' value=$title />
            </label>
          </p>
          <p style='text-align:right;'>
            <label for='newstatpresstopposts-howmany'>". __('Limit results to','newstatpress') ."
            <input style='width: 100px;' id='newstatpresstopposts-howmany' name='newstatpresstopposts-howmany' type='text' value=$howmany />
            </label>
          </p>";
    echo '<p style="text-align:right;"><label for="newstatpresstopposts-showcounts">' . __('Visits','newstatpress') . ' <input id="newstatpresstopposts-showcounts" name="newstatpresstopposts-showcounts" type=checkbox value="checked" '.$showcounts.' /></label></p>';
    echo '<input type="hidden" id="newstatpress-submitTopPosts" name="newstatpresstopposts-submit" value="1" />';
  }
  function nsp_WidgetTopPosts($args) {
    extract($args);
    $options = get_option('widget_newstatpresstopposts');
    $title = htmlspecialchars($options['title'], ENT_QUOTES);
    $howmany = htmlspecialchars($options['howmany'], ENT_QUOTES);
    $showcounts = htmlspecialchars($options['showcounts'], ENT_QUOTES);
    echo $before_widget;
    print($before_title . $title . $after_title);
    print nsp_TopPosts($howmany,$showcounts);
    echo $after_widget;
  }
  wp_register_sidebar_widget('NewStatPress TopPosts', 'NewStatPress TopPosts', 'nsp_WidgetTopPosts');
  wp_register_widget_control('NewStatPress TopPosts', array('NewStatPress TopPosts','widgets'), 'nsp_WidgetTopPosts_control', 300, 110);
}
add_action('plugins_loaded', 'nsp_WidgetInit');


/**
 * Replace a content in page with NewStatPress output
 * Used format is: [NewStatPress: type]
 * Type can be:
 *  [NewStatPress: Overview]
 *  [NewStatPress: Top days]
 *  [NewStatPress: O.S.]
 *  [NewStatPress: Browser]
 *  [NewStatPress: Feeds]
 *  [NewStatPress: Search Engine]
 *  [NewStatPress: Search terms]
 *  [NewStatPress: Top referrer]
 *  [NewStatPress: Languages]
 *  [NewStatPress: Spider]
 *  [NewStatPress: Top Pages]
 *  [NewStatPress: Top Days - Unique visitors]
 *  [NewStatPress: Top Days - Pageviews]
 *  [NewStatPress: Top IPs - Pageviews]
 *
 * @param content the content of page
 ******************************************************/
function nsp_Shortcode($content = '') {
  ob_start();
  $TYPEs = array();
  $TYPE = preg_match_all('/\[NewStatPress: (.*)\]/Ui', $content, $TYPEs);

  foreach ($TYPEs[1] as $k => $TYPE) {
    switch ($TYPE) {
      case "Overview":
        $replacement=nsp_MakeOverview(FALSE);
        break;
      case "Top days":
        $replacement=nsp_GetDataQuery2("date","Top days", (get_option('newstatpress_el_top_days')=='') ? 5:get_option('newstatpress_el_top_days'), FALSE);
        break;
      case "O.S.":
        $replacement=nsp_GetDataQuery2("os","O.S.",(get_option('newstatpress_el_os')=='') ? 10:get_option('newstatpress_el_os'),"","","AND feed='' AND spider='' AND os<>''", FALSE);
        break;
      case "Browser":
        $replacement=nsp_GetDataQuery2("browser","Browser",(get_option('newstatpress_el_browser')=='') ? 10:get_option('newstatpress_el_browser'),"","","AND feed='' AND spider='' AND browser<>''", FALSE);
        break;
      case "Feeds":
        $replacement=nsp_GetDataQuery2("feed","Feeds",(get_option('newstatpress_el_feed')=='') ? 5:get_option('newstatpress_el_feed'),"","","AND feed<>''", FALSE);
        break;
      case "Search Engine":
        $replacement=nsp_GetDataQuery2("searchengine","Search engines",(get_option('newstatpress_el_searchengine')=='') ? 10:get_option('newstatpress_el_searchengine'),"","","AND searchengine<>''", FALSE);
        break;
      case "Search terms":
        $replacement=nsp_GetDataQuery2("search","Top search terms",(get_option('newstatpress_el_search')=='') ? 20:get_option('newstatpress_el_search'),"","","AND search<>''", FALSE);
        break;
      case "Top referrer":
        $replacement= nsp_GetDataQuery2("referrer","Top referrer",(get_option('newstatpress_el_referrer')=='') ? 10:get_option('newstatpress_el_referrer'),"","","AND referrer<>'' AND referrer NOT LIKE '%".get_bloginfo('url')."%'", FALSE);
        break;
      case "Languages":
        $replacement=nsp_GetDataQuery2("nation","Countries/Languages",(get_option('newstatpress_el_languages')=='') ? 20:get_option('newstatpress_el_languages'),"","","AND nation<>'' AND spider=''", FALSE);
        break;
      case "Spider":
        $replacement=nsp_GetDataQuery2("spider","Spiders",(get_option('newstatpress_el_spiders')=='') ? 10:get_option('newstatpress_el_spiders'),"","","AND spider<>''", FALSE);
        break;
      case "Top Pages":
        $replacement=nsp_GetDataQuery2("urlrequested","Top pages",(get_option('newstatpress_el_pages')=='') ? 5:get_option('newstatpress_el_pages'),"","urlrequested","AND feed='' and spider=''", FALSE);
        break;
      case "Top Days - Unique visitors":
        $replacement=nsp_GetDataQuery2("date","Top Days - Unique visitors",(get_option('newstatpress_el_visitors')=='') ? 5:get_option('newstatpress_el_visitors'),"distinct","ip","AND feed='' and spider=''", FALSE);
        break;
      case "Top Days - Pageviews":
        $replacement=nsp_GetDataQuery2("date","Top Days - Pageviews",(get_option('newstatpress_el_daypages')=='') ? 5:get_option('newstatpress_el_daypages'),"","urlrequested","AND feed='' and spider=''", FALSE);
        break;
      case "Top IPs - Pageviews":
        $replacement=nsp_GetDataQuery2("ip","Top IPs - Pageviews",(get_option('newstatpress_el_ippages')=='') ? 5:get_option('newstatpress_el_ippages'),"","urlrequested","AND feed='' and spider=''", FALSE);
        break;
      default:
        $replacement="";
    }
    $content = str_replace($TYPEs[0][$k], $replacement, $content);
  }
  ob_get_clean();
  return $content;
}
add_filter('the_content', 'nsp_Shortcode');

function nsp_CalculateVariation($month,$lmonth,$row) {

  $target = round($month->$row / (
    (date("d", current_time('timestamp')) - 1 +
    (date("H", current_time('timestamp')) +
    (date("i", current_time('timestamp')) + 1)/ 60.0) / 24.0)) * date("t", current_time('timestamp'))
  );

  $month->change = null;
  $added = null;

  if($lmonth->$row <> 0) {
    $percent_change = round( 100 * ($month->$row / $lmonth->$row ) - 100,1);
    $percent_target = round( 100 * ($target / $lmonth->$row ) - 100,1);

    if($percent_change >= 0) {
      $percent_change=sprintf("+%'04.1f", $percent_target);
      $month->change = "<td class='coll'><code style='color:green'>($percent_change%)</code></td>";
    }
    else {
      $percent_change=sprintf("%'05.1f", $percent_change);
      $month->change = "<td class='coll'><code style='color:red'>($percent_change%)</code></td>";
    }

    if($percent_target >= 0) {
      $percent_target=sprintf("+%'04.1f", $percent_target);
      $added = "<td class='coll'><code style='color:green'>($percent_target%)</code></td>";
    }
    else {
      $percent_target=sprintf("%'05.1f", $percent_target);
      $added = "<td class='coll'><code style='color:red'>($percent_target%)</code></td>";
    }
  }
  else {
    $month->change = "<td></td>";
    $added = "<td class='coll'></td>";
  }

  $calculated_result=array($month->change,$target,$added);
  return $calculated_result;
}

function nsp_MakeOverview($print ='dashboard') {

  global $wpdb, $nsp_option_vars;
  $table_name = nsp_TABLENAME;

  $overview_table='';

  // $since = NewStatPress_Print('%since%');
  $since = nsp_ExpandVarsInsideCode('%since%');
  $lastmonth = nsp_Lastmonth();
  $thisyear = gmdate('Y', current_time('timestamp'));
  $thismonth = gmdate('Ym', current_time('timestamp'));
  $yesterday = gmdate('Ymd', current_time('timestamp')-86400);
  $today = gmdate('Ymd', current_time('timestamp'));
  $tlm[0]=substr($lastmonth,0,4); $tlm[1]=substr($lastmonth,4,2);

  $thisyearHeader = gmdate('Y', current_time('timestamp'));
  $lastmonthHeader = gmdate('M, Y',gmmktime(0,0,0,$tlm[1],1,$tlm[0]));
  $thismonthHeader = gmdate('M, Y', current_time('timestamp'));
  $yesterdayHeader = gmdate('d M', current_time('timestamp')-86400);
  $todayHeader = gmdate('d M', current_time('timestamp'));

  // build head table overview
  if ($print=='main') {
    $overview_table.="<div class='wrap'><h2>". __('Overview','newstatpress'). "</h2>";
    $overview_table.="<table class='widefat center nsp'>
              <thead>
              <tr class='sup'>
                <th></th>
                <th>". __('Total since','newstatpress'). "</th>
                <th scope='col'>". __('This year','newstatpress'). "</th>
                <th scope='col'>". __('Last month','newstatpress'). "</th>
                <th scope='col' colspan='2'>". __('This month','newstatpress'). "</th>
                <th scope='col' colspan='2'>". __('Target This month','newstatpress'). "</th>
                <th scope='col'>". __('Yesterday','newstatpress'). "</th>
                <th scope='col'>". __('Today','newstatpress'). "</th>
              </tr>
              <tr class='inf'>
                <th></th>
                <th><span>$since</span></th>
                <th><span>$thisyearHeader</span></th>
                <th><span>$lastmonthHeader</span></th>
                <th colspan='2'><span > $thismonthHeader </span></th>
                <th colspan='2'><span > $thismonthHeader </span></th>
                <th><span>$yesterdayHeader</span></th>
                <th><span>$todayHeader</span></th>
              </tr></thead>
              <tbody class='overview-list'>";
  }
  elseif ($print=='dashboard') {
   $overview_table.="<table class='widefat center nsp'>
                      <thead>
                      <tr class='sup dashboard'>
                      <th></th>
                          <th scope='col'>". __('M-1','newstatpress'). "</th>
                          <th scope='col' colspan='2'>". __('M','newstatpress'). "</th>
                          <th scope='col'>". __('Y','newstatpress'). "</th>
                          <th scope='col'>". __('T','newstatpress'). "</th>
                      </tr>
                      <tr class='inf dashboard'>
                      <th></th>
                          <th><span>$lastmonthHeader</span></th>
                          <th colspan='2'><span > $thismonthHeader </span></th>
                          <th><span>$yesterdayHeader</span></th>
                          <th><span>$todayHeader</span></th>
                      </tr></thead>
                      <tbody class='overview-list'>";
  }

  // build body table overview
  $overview_rows=array('visitors','visitors_feeds','pageview','feeds','spiders');

  foreach ($overview_rows as $row) {

    switch($row) {

      case 'visitors' :
        $row2='DISTINCT ip';
        $row_title=__('Visitors','newstatpress');
        $sql_QueryTotal="SELECT count($row2) AS $row FROM $table_name WHERE feed='' AND spider=''";
      break;

      case 'visitors_feeds' :
        $row2='DISTINCT ip';
        $row_title=__('Visitors through Feeds','newstatpress');
        $sql_QueryTotal="SELECT count($row2) AS $row FROM $table_name WHERE feed<>'' AND spider='' AND agent<>''";
        break;

      case 'pageview' :
        $row2='date';
        $row_title=__('Pageviews','newstatpress');
        $sql_QueryTotal="SELECT count($row2) AS $row FROM $table_name WHERE feed='' AND spider=''";
      break;

      case 'spiders' :
        $row2='date';
        $row_title=__('Spiders','newstatpress');
        $sql_QueryTotal="SELECT count($row2) AS $row FROM $table_name WHERE feed='' AND spider<>''";
      break;

      case 'feeds' :
        $row2='date';
        $row_title=__('Pageviews through Feeds','newstatpress');
        $sql_QueryTotal="SELECT count($row2) AS $row FROM $table_name WHERE feed<>'' AND spider=''";
      break;
    }

    // query requests
    $qry_total = $wpdb->get_row($sql_QueryTotal);
    $qry_tyear = $wpdb->get_row($sql_QueryTotal. " AND date LIKE '$thisyear%'");


    if (get_option($nsp_option_vars['calculation']['name'])=='sum') {

      // alternative calculation by mouth: sum of unique visitors of each day
      $tot=0;
      $t = getdate(current_time('timestamp'));
      $year = $t['year'];
      $month = sprintf('%02d', $t['mon']);
      $day= $t['mday'];
      $totlm=0;

      for($k=$t['mon'];$k>0;$k--)
      {
        //current month

      }
      for($i=0;$i<$day;$i++)
      {
        $qry_daylmonth = $wpdb->get_row($sql_QueryTotal. " AND date LIKE '$lastmonth$i%'");
        $qry_day=$wpdb->get_row($sql_QueryTotal. " AND date LIKE '$year$month$i%'");
        $tot+=$qry_day->$row;
        $totlm+=$qry_daylmonth->$row;

      }
      // echo $totlm." ,";
      $qry_tmonth->$row=$tot;
      $qry_lmonth->$row=$totlm;

    }
    else { // classic
      $qry_tmonth = $wpdb->get_row($sql_QueryTotal. " AND date LIKE '$thismonth%'");
      $qry_lmonth = $wpdb->get_row($sql_QueryTotal. " AND date LIKE '$lastmonth%'");
    }


    $qry_y = $wpdb->get_row($sql_QueryTotal. " AND date LIKE '$yesterday'");
    $qry_t = $wpdb->get_row($sql_QueryTotal. " AND date LIKE '$today'");

    $calculated_result=nsp_CalculateVariation($qry_tmonth,$qry_lmonth,$row);

    // build full current row
    $overview_table.="<tr><td class='row_title $row'>$row_title</td>";
    if ($print=='main')
      $overview_table.="<td class='colc'>".$qry_total->$row."</td>\n";
    if ($print=='main')
      $overview_table.="<td class='colc'>".$qry_tyear->$row."</td>\n";
    $overview_table.="<td class='colc'>".$qry_lmonth->$row."</td>\n";
    $overview_table.="<td class='colr'>".$qry_tmonth->$row. $calculated_result[0] ."</td>\n";
    if ($print=='main')
      $overview_table.="<td class='colr'> $calculated_result[1] $calculated_result[2] </td>\n";
    $overview_table.="<td class='colc'>".$qry_y->$row."</td>\n";
    $overview_table.="<td class='colc'>".$qry_t->$row."</td>\n";
    $overview_table.="</tr>";
  }

  if ($print=='dashboard'){
    $overview_table.="</tr></table>";
  }

  if ($print=='main'){
    $overview_table.= "</tr></table>\n";

    // print graph
    //  last "N" days graph  NEW
    $gdays=get_option('newstatpress_daysinoverviewgraph'); if($gdays == 0) { $gdays=20; }
    $start_of_week = get_option('start_of_week');
    $qry = $wpdb->get_row("
      SELECT count(date) as pageview, date
      FROM $table_name
      GROUP BY date HAVING date >= '".gmdate('Ymd', current_time('timestamp')-86400*$gdays)."'
      ORDER BY pageview DESC
      LIMIT 1
    ");

    $maxxday = 0;
    if ($qry != null) $maxxday=$qry->pageview;
    if($maxxday == 0) { $maxxday = 1; }
    # Y
    $gd=(90/$gdays).'%';

    $overview_graph="<table class='graph'><tr>";

    for($gg=$gdays-1;$gg>=0;$gg--) {

      $scale_factor=2; //2 : 200px in CSS

      $date=gmdate('Ymd', current_time('timestamp')-86400*$gg);

      $qry_visitors = $wpdb->get_row("SELECT count(DISTINCT ip) AS total FROM $table_name WHERE feed='' AND spider='' AND date = '$date'");
      $px_visitors = $scale_factor*(round($qry_visitors->total*100/$maxxday));

      $qry_pageviews = $wpdb->get_row("SELECT count(date) AS total FROM $table_name WHERE feed='' AND spider='' AND date = '$date'");
      $px_pageviews = $scale_factor*(round($qry_pageviews->total*100/$maxxday));

      $qry_spiders = $wpdb->get_row("SELECT count(date) AS total FROM $table_name WHERE feed='' AND spider<>'' AND date = '$date'");
      $px_spiders = $scale_factor*(round($qry_spiders->total*100/$maxxday));

      $qry_feeds = $wpdb->get_row("SELECT count(date) AS total FROM $table_name WHERE feed<>'' AND spider='' AND date = '$date'");
      $px_feeds = $scale_factor*(round($qry_feeds->total*100/$maxxday));

      $px_white = $scale_factor*100 - $px_feeds - $px_spiders - $px_pageviews - $px_visitors;

      $overview_graph.="<td width='$gd' valign='bottom'>";

      $overview_graph.="<div class='overview-graph'>
        <div style='border-left:1px; background:#ffffff;width:100%;height:".$px_white."px;'></div>
        <div class='visitors_bar' style='height:".$px_visitors."px;' title='".$qry_visitors->total." ".__('Visitors','newstatpress')."'></div>
        <div class='web_bar' style='height:".$px_pageviews."px;' title='".$qry_pageviews->total." ".__('Pageviews','newstatpress')."'></div>
        <div class='spiders_bar' style='height:".$px_spiders."px;' title='".$qry_spiders->total." ".__('Spiders','newstatpress')."'></div>
        <div class='feeds_bar' style='height:".$px_feeds."px;' title='".$qry_feeds->total." ".__('Feeds','newstatpress')."'></div>
        <div style='background:gray;width:100%;height:1px;'></div>";
        if($start_of_week == gmdate('w',current_time('timestamp')-86400*$gg)) $overview_graph.="<div class='legend-W'>";
        else $overview_graph.="<div class='legend'>";
        $overview_graph.=gmdate('d', current_time('timestamp')-86400*$gg) . ' ' . gmdate('M', current_time('timestamp')-86400*$gg) .     "</div></div></td>\n";
    }
    $overview_graph.="</tr></table></div>";

    $overview_table=$overview_table.$overview_graph;
  }

  if ($print!=FALSE) print $overview_table;
  else return $overview_table;
}

register_activation_hook(__FILE__,'nsp_BuildPluginSQLTable');

?>
