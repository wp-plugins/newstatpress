<?php
/*
Plugin Name: NewStatPress
Plugin URI: http://newstatpress.altervista.org
Description: Real time stats for your Wordpress blog
Version: 0.9.2
Author: Stefano Tognon and cHab (from Daniele Lippi works)
Author URI: http://newstatpress.altervista.org
*/

$_NEWSTATPRESS['version']='0.9.2';
$_NEWSTATPRESS['feedtype']='';

global $newstatpress_dir, $option_list_info;

$newstatpress_dir = WP_PLUGIN_DIR . '/' .dirname(plugin_basename(__FILE__));

$option_list_info=array( // list of option variable name, with default value associated
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
  'ippages'=>array('name'=>'newstatpress_el_ippages','value'=>'5')
);


/**
 * add by chab
 *
 * Add the plugin CSS style only on admin page
 * TODO :  use your function for old version of WP
 */
  function register_plugin_styles() {

      $style_path=plugins_url('./css/style.css', __FILE__);

      wp_register_style('NewStatPressStyles', $style_path);
      wp_enqueue_style('NewStatPressStyles');
  }
  add_action( 'admin_enqueue_scripts', 'register_plugin_styles' );

/**
 * add by chab
 *
 * iriNewStatPressCredits() — credit menu page
 * iriNewStatPressRemove() — remove menu page
 */
require ('includes/functions-extra.php');


/**
 * Get the url of the plugin
 *
 * @return the url of the plugin
 */
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
 * Add pages with NewStatPress commands
 */
function iri_add_pages() {
  // Create/update table if it not exists
  global $wpdb;
  $table_name = $wpdb->prefix . "statpress";
  if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    iri_NewStatPress_CreateTable();
  }

  // check the level fixed
  $mincap=get_option('newstatpress_mincap');
  if($mincap == '') {
    $mincap="level_8";
  }

  // ORIG   add_submenu_page('index.php', 'StatPress', 'StatPress', 8, 'statpress', 'iriNewStatPress');

  add_submenu_page('NSP-main', __('Overview','newstatpress'), __('Overview','newstatpress'), $mincap, 'NSP-main', 'iriNewStatPressMain');
  add_menu_page('NewStatPres', 'NewStatPress', $mincap, 'NSP-main', 'iriNewStatPressMain', plugins_url('newstatpress/images/stat.png',dirname(plugin_basename(__FILE__))));

  add_submenu_page('NSP-main', __('Details','newstatpress'), __('Details','newstatpress'), $mincap, 'details-page', 'iriNewStatPressDetails');
  add_submenu_page('NSP-main', __('Last visitors by Spy','newstatpress'), __('Last visitors by Spy','newstatpress'), $mincap, 'spy-page', 'iriNewStatPressSpy');
  add_submenu_page('NSP-main', __('Visitors by Spy','newstatpress'), __('Visitors by Spy','newstatpress'), $mincap, 'newspy-page', 'iriNewStatPressNewSpy');
  add_submenu_page('NSP-main', __('Spy Bot','newstatpress'), __('Spy Bot','newstatpress'), $mincap, 'spybot-page', 'iriNewStatPressSpyBot');
  add_submenu_page('NSP-main', __('Search','newstatpress'), __('Search','newstatpress'), $mincap, 'search-page', 'iriNewStatPressSearch');
  add_submenu_page('NSP-main', __('Export','newstatpress'), __('Export','newstatpress'), $mincap, 'export-page', 'iriNewStatPressExport');
  add_submenu_page('NSP-main', __('Options','newstatpress'), __('Options','newstatpress'), $mincap, 'options-page', 'iriNewStatPressOptions');
  add_submenu_page('NSP-main', __('Credits','newstatpress'), __('Credits','newstatpress'), $mincap, 'credits-page', 'iriNewStatPressCredits');
  add_submenu_page('NSP-main', __('Remove','newstatpress'), __('Remove','newstatpress'), $mincap,  'remove-page', 'iriNewStatPressRemove');
}
add_action('admin_menu', 'iri_add_pages');


/**
 * Filter the given value for preventing XSS attacks
 *
 * @param _value the value to filter
 * @return filtered value
 */
function iriNewStatPress_filter_for_xss($_value){
  $_value=trim($_value);

  // Avoid XSS attacks
  $clean_value = preg_replace('/[^a-zA-Z0-9\,\.\/\ \-\_\?=&;]/', '', $_value);
  if (strlen($_value)==0){
    return array();
  } else {
      $array_values = explode(',',$clean_value);
      array_walk($array_values, 'iriNewStatPress_trim_value');
      return $array_values;
    }
}

/**
 * Trim the given string
 */
function iriNewStatPress_trim_value(&$value) {
  $value = trim($value);
}


function print_option($option_title,$option_var,$var) {

  echo "<tr>\n<td>$option_title</td>\n";
  echo "<td><select name=$option_var>\n";
  if($option_var=='newstatpress_mincap') {
    $role = get_role('administrator');
    foreach($role->capabilities as $cap => $grant) {
      print "<option ";
      if($var == $cap) {
        print "selected ";
      }
      print ">$cap</option>";
    }
  } else {
    foreach($var as $option) {
      // list($i,$j) = $option;
      echo "<option value=$option[0]";
      if(get_option($option_var)==$option[0]) {
        echo " selected";
      }
      echo ">". $option[0];
      if ($option[1] !=  '') {
        echo " ";
        _e($option[1],'newstatpress');
      }
      echo "</option>\n";
    }
  }
  echo "</select></td></tr>";
}

// add by chab
function print_row_input($option_title,$option_list_info,$input_size,$input_maxlength) {
  echo "<tr><td><label for=$option_list_info[name]>$option_title</label></td>\n";
  echo "<td><input class='right' type='text' name=$option_list_info[name] value=";
  echo (get_option($option_list_info['name'])=='') ? $option_list_info['value']:get_option($option_list_info['name']);
  echo " size=$input_size maxlength=$input_maxlength />\n</td></tr>\n";
}

function print_row($option_title) {
  echo "<tr><td>$option_title</td></tr>\n";
}

// add by chab
function print_checked($option_title,$option_var) {
  echo "<tr><td><input type=checkbox name='$option_var' value='checked' ".get_option($option_var)."> $option_title</td></tr>\n";
}

// add by chab
function print_textaera($option_title,$option_var,$option_description) {
  echo "<tr><td>\n<h4><label for=$option_var>$option_title</label></h4>\n";
  echo "<p>$option_description</p>\n";
  echo "<p><textarea class='large-text code' cols='40' rows='2' name=$option_var id=$option_var>";
  echo implode(',', get_option($option_var,array()));
  echo "</textarea></p>\n";
  echo "</td></tr>\n";
}


// function ilc_admin_tabs( $current = 'homepage' ) {
//     $tabs = array( 'homepage' => 'Newstatpress Settings', 'general' => 'General', 'ip2nation' => 'IP2nation', 'database' => 'Update Database' );
//     echo '<div id="icon-themes" class="icon32"><br></div>';
//     echo '<h2 class="nav-tab-wrapper">';
//     foreach( $tabs as $tab => $name ){
//         $class = ( $tab == $current ) ? ' nav-tab-active' : '';
//         echo "<a class='nav-tab $class' href='?page=theme-settings&tab=$tab'>$name</a>";
//
//     }
//     echo '</h2>';
// }

/**
 * Generate HTML for option menu in Wordpress
 */
function iriNewStatPressOptions() {

  if(isset($_POST['saveit']) && $_POST['saveit'] == 'yes') { //option update request by user

    $i=isset($_POST['newstatpress_collectloggeduser']) ? $_POST['newstatpress_collectloggeduser'] : '';
    update_option('newstatpress_collectloggeduser', $i);

    $i=isset($_POST['newstatpress_donotcollectspider']) ? $_POST['newstatpress_donotcollectspider'] : '';
    update_option('newstatpress_donotcollectspider', $i);

    $i=isset($_POST['newstatpress_cryptip']) ? $_POST['newstatpress_cryptip'] : '';
    update_option('newstatpress_cryptip', $i);

    $i=isset($_POST['newstatpress_dashboard']) ? $_POST['newstatpress_dashboard'] : '';
    update_option('newstatpress_dashboard', $i);

    update_option('newstatpress_ip_per_page_newspy', $_POST['newstatpress_ip_per_page_newspy']);
    update_option('newstatpress_visits_per_ip_newspy', $_POST['newstatpress_visits_per_ip_newspy']);
    update_option('newstatpress_bot_per_page_spybot', $_POST['newstatpress_bot_per_page_spybot']);
    update_option('newstatpress_visits_per_bot_spybot', $_POST['newstatpress_visits_per_bot_spybot']);
    update_option('newstatpress_autodelete', $_POST['newstatpress_autodelete']);
    update_option('newstatpress_autodelete_spiders', $_POST['newstatpress_autodelete_spiders']);
    update_option('newstatpress_daysinoverviewgraph', $_POST['newstatpress_daysinoverviewgraph']);
    update_option('newstatpress_mincap', $_POST['newstatpress_mincap']);
    update_option('newstatpress_ignore_users', iriNewStatPress_filter_for_xss($_POST['newstatpress_ignore_users']));
    update_option('newstatpress_ignore_ip', iriNewStatPress_filter_for_xss($_POST['newstatpress_ignore_ip']));
    update_option('newstatpress_ignore_permalink', iriNewStatPress_filter_for_xss($_POST['newstatpress_ignore_permalink']));
    update_option('newstatpress_el_overview', $_POST['newstatpress_el_overview']);
    update_option('newstatpress_el_top_days', $_POST['newstatpress_el_top_days']);
    update_option('newstatpress_el_os', $_POST['newstatpress_el_os']);
    update_option('newstatpress_el_browser', $_POST['newstatpress_el_browser']);
    update_option('newstatpress_el_feed', $_POST['newstatpress_el_feed']);
    update_option('newstatpress_el_searchengine', $_POST['newstatpress_el_searchengine']);
    update_option('newstatpress_el_search', $_POST['newstatpress_el_search']);
    update_option('newstatpress_el_referrer', $_POST['newstatpress_el_referrer']);
    update_option('newstatpress_el_languages', $_POST['newstatpress_el_languages']);
    update_option('newstatpress_el_spiders', $_POST['newstatpress_el_spiders']);
    update_option('newstatpress_el_pages', $_POST['newstatpress_el_pages']);
    update_option('newstatpress_el_visitors', $_POST['newstatpress_el_visitors']);
    update_option('newstatpress_el_daypages', $_POST['newstatpress_el_daypages']);
    update_option('newstatpress_el_ippages', $_POST['newstatpress_el_ippages']);
    update_option('newstatpress_updateint', $_POST['newstatpress_updateint']);

    // update database too and print message confirmation
    iri_NewStatPress_CreateTable();
    print "<br /><div class='updated'><p>".__('Options saved!','newstatpress')."</p></div>";
  }
  ?>
  <div id='settings' class='wrap'><h2><?php _e('NewStatPress Settings','newstatpress'); ?></h2>

    <!--IP2nation & update database  -->
    <?php
    // Importation if requested by user
    if (isset($_POST['download']) && $_POST['download'] == 'yes' ) {
      $install_result=iriNewStatPressIP2nationDownload();
    }

    // database update if requested by user
    if (isset($_POST['update']) && $_POST['update'] == 'yes' ) {
      iriNewStatPressUpdate();
      die;
    }

    // TODO chab: To add routine to check if IP2nation is already installed
    // if YES => to check if it's the last version to avoid the download if not necessary
    ?>
    <!-- IP2nation -->
    <div class='wrap'><h3><?php _e('To import IP2nation database','newstatpress'); ?></h3>

      <?php
      if ($install_result !='') {
        print "<br /><div class='updated'><p>".__($install_result,'newstatpress')."</p></div>";
      }

      $file_ip2nation= WP_PLUGIN_DIR . '/' .dirname(plugin_basename(__FILE__)) . '/includes/ip2nation.sql';
      if (file_exists($file_ip2nation)) {
        $i=sprintf(__('Last version installed: %s','newstatpress'), date('d/m/Y', filemtime($file_ip2nation)));
        echo $i.'<br /><br />';
        _e('To update the IP2nation database, just click on the button bellow.','newstatpress');
        $button_name='Update';
      }
      else {
        _e('Last version installed: none ','newstatpress');
        echo '<br /><br />';
        _e('To download and to install the IP2nation database, just click on the button bellow.','newstatpress');
        $button_name='Download';
      }
      ?>
      <br /><br />
      <form method=post>
        <input type=hidden name=page value=newstatpress>
        <input type=hidden name=download value=yes>
        <input type=hidden name=newstatpress_action value=ip2nation>
        <button class='button button-primary' type=submit><?php _e($button_name,'newstatpress'); ?></button>
      </form>

    </div>

    <div class='wrap'><h3><?php _e('Database update','newstatpress'); ?></h3>
      <?php
      _e('To update the newstatpress database, just click on the button bellow.','newstatpress');
      ?>
      <br /><br />
      <form method=post>
        <input type=hidden name=page value=newstatpress>
        <input type=hidden name=update value=yes>
        <input type=hidden name=newstatpress_action value=update>
        <button class='button button-primary' type=submit><?php _e('Update','newstatpress'); ?></button>
      </form>
    </div>


    <form method=post>

      <!-- General option -->
      <h3><?php _e('General option','newstatpress'); ?></h3>
      <table class='table-option'>
        <?php

        global $option_list_info;

        // input parameters
        $input_size='2';
        $input_maxlength='3';

        // traduction $variable addition for Poedit parsing
        __('Never','newstatpress');
        __('All','newstatpress');
        __('month','newstatpress');
        __('months','newstatpress');
        __('week','newstatpress');
        __('weeks','newstatpress');

        $option_title=__('Collect data about logged users, too.','newstatpress');
        $option_var='newstatpress_collectloggeduser';
        print_checked($option_title,$option_var);

        $option_title=__('Do not collect spiders visits','newstatpress');
        $option_var='newstatpress_donotcollectspider';
        print_checked($option_title,$option_var);

        $option_title=__('Crypt IP addresses','newstatpress');
        $option_var='newstatpress_cryptip';
        print_checked($option_title,$option_var);

        $option_title=__('Show NewStatPress dashboard widget','newstatpress');
        $option_var='newstatpress_dashboard';
        print_checked($option_title,$option_var);

        $option_title=sprintf(__('Elements in Overview (default %d)','newstatpress'), $option_list_info['overview']['value']);
        print_row_input($option_title,$option_list_info['overview'],$input_size,$input_maxlength);

        // $val=array(['20',''], ['50',''], ['100','']);
        $val=array(array(20,''),array(50,''),array(100,''));
        $option_title=__('Visitors by Spy: number of IP per page','newstatpress');
        $option_var='newstatpress_ip_per_page_newspy';
        print_option($option_title,$option_var,$val);

        $option_title=__('Visitors by Spy: number of visits for IP','newstatpress');
        $option_var='newstatpress_visits_per_ip_newspy';
        print_option($option_title,$option_var,$val);

        $option_title=__('Spy Bot: number of bot per page','newstatpress');
        $option_var='newstatpress_bot_per_page_spybot';
        print_option($option_title,$option_var,$val);

        $option_title=__('Spy Bot: number of bot for IP','newstatpress');
        $option_var='newstatpress_visits_per_bot_spybot';
        print_option($option_title,$option_var,$val);

        $val=array(array('', 'Never'),array(1, 'month'),array(3, 'months'),array(6, 'months'),array(12, 'months'));
        $option_title=__('Automatically delete visits older than','newstatpress');
        $option_var='newstatpress_autodelete';
        print_option($option_title,$option_var,$val);

        $option_title=__('Automatically delete only spiders visits older than','newstatpress');
        $option_var='newstatpress_autodelete_spiders';
        print_option($option_title,$option_var,$val);

        // $val= array(['7',''],['10',''],['20',''],['30',''],['50','']);
        $val=array(array(7,''),array(10,''),array(20,''),array(30,''),array(50,''));
        $option_title=__('Days number in Overview graph','newstatpress');
        $option_var='newstatpress_daysinoverviewgraph';
        print_option($option_title,$option_var,$val);

        $option_title=__('Minimum capability to view stats','newstatpress')." (<a href='http://codex.wordpress.org/Roles_and_Capabilities' target='_blank'>".__("more info",'newstatpress')."</a>)";
        $option_var='newstatpress_mincap';
        $val=get_option('newstatpress_mincap');
        print_option($option_title,$option_var,$val);

        ?>
      </table>

      <!-- Parameters to ignore -->
      <h3><?php _e('Parameters to ignore','newstatpress') ?></h3>
      <table class='option2'>
        <?php

        $option_title=__('Logged users to ignore','newstatpress');
        $option_var='newstatpress_ignore_users';
        $option_description=__('Enter a list of users you don\'t want to track, separated by commas, even if collect data about logged users is on','newstatpress');
        print_textaera($option_title,$option_var,$option_description);


        $option_title=__('IP addresses to ignore','newstatpress');
        $option_var='newstatpress_ignore_ip';
        $option_description=__('Enter a list of networks you don\'t want to track, separated by commas. Each network <strong>must</strong> be defined using the CIDR notation (i.e. <em>192.168.1.1/24</em>). <br />If the format is incorrect, NewStatPress may not track pageviews properly.','newstatpress');
        print_textaera($option_title,$option_var,$option_description);

        $option_title=__('Pages and posts to ignore','newstatpress');
        $option_var='newstatpress_ignore_permalink';
        $option_description=__('Enter a list of permalinks you don\'t want to track, separated by commas. You should omit the domain name from these resources: <em>/about, p=1</em>, etc. <br />NewStatPress will ignore all the pageviews whose permalink <strong>contains</strong> at least one of them.','newstatpress');
        print_textaera($option_title,$option_var,$option_description);

        ?>
      </table>

      <!-- Details menu options -->
      <h3><label for="newstatpress_details_options"><?php _e('Details menu options','newstatpress') ?></label></h3>
      <table>
        <?php

        $option_title=sprintf(__('Elements in Top days (default %d)','newstatpress'), $option_list_info['top_days']['value']);
        print_row_input($option_title,$option_list_info['top_days'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in O.S. (default %d)','newstatpress'), $option_list_info['os']['value']);
        print_row_input($option_title,$option_list_info['os'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Browser (default %d)','newstatpress'), $option_list_info['browser']['value']);
        print_row_input($option_title,$option_list_info['browser'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Feed (default %d)','newstatpress'), $option_list_info['feed']['value']);
        print_row_input($option_title,$option_list_info['feed'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Search Engines (default %d)','newstatpress'), $option_list_info['searchengine']['value']);
        print_row_input($option_title,$option_list_info['searchengine'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Top Search Terms (default %d)','newstatpress'), $option_list_info['search']['value']);
        print_row_input($option_title,$option_list_info['search'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Top Referrer (default %d)','newstatpress'), $option_list_info['referrer']['value']);
        print_row_input($option_title,$option_list_info['referrer'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Countries/Languages (default %d)','newstatpress'), $option_list_info['languages']['value']);
        print_row_input($option_title,$option_list_info['languages'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Spiders (default %d)','newstatpress'), $option_list_info['spiders']['value']);
        print_row_input($option_title,$option_list_info['spiders'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Top Pages (default %d)','newstatpress'), $option_list_info['pages']['value']);
        print_row_input($option_title,$option_list_info['pages'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Top Days - Unique visitors (default %d)','newstatpress'), $option_list_info['visitors']['value']);
        print_row_input($option_title,$option_list_info['visitors'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Top Days - Pageviews (default %d)','newstatpress'), $option_list_info['daypages']['value']);
        print_row_input($option_title,$option_list_info['daypages'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Top IPs - Pageviews (default %d)', 'newstatpress'), $option_list_info['ippages']['value']);
        print_row_input($option_title,$option_list_info['ippages'],$input_size,$input_maxlength);

        ?>
      </table>

      <h3><?php _e('Database update option','newstatpress'); ?></h3>
      <table>
        <p  class='table-databaseupdate'>
          <?php
          _e('Select the interval of date from today you want to use for updating your database with new definitions. ','newstatpress');
          _e('Be aware, larger is the interval, longer is the update and bigger are the resources required.','newstatpress');
          // _e('You can choose to not update some fields if you want.','newstatpress')
          ?>
       </p>

       <?php
       $val= array(array('', 'All'),array(1, 'week'),array(2, 'weeks'),array(3, 'weeks'),array(1, 'month'),array(2, 'months'),array(3, 'months'),array(6, 'months'),array(9, 'months'),array(12, 'months'));
       $option_title=__('Update data in the given period','newstatpress');
       $option_var='newstatpress_updateint';
       print_option($option_title,$option_var,$val);
       ?>

      <tr><td><br><input class='button button-primary' type=submit value="<?php _e('Save options','newstatpress'); ?>"></td></tr>
      </table>
        <input type=hidden name=saveit value=yes>
        <input type=hidden name=page value=newstatpress><input type=hidden name=newstatpress_action value=options>
      </form>
      </div>
      <?php
    }


  // add by chab
  function iriNewStatPressIP2nationDownload() {

      //Request to make http request with WP functions
      if( !class_exists( 'WP_Http' ) ) {
        include_once( ABSPATH . WPINC. '/class-http.php' );
      }

      // Definition $var
      $timeout=300;
      $db_file_url = 'http://www.ip2nation.com/ip2nation.zip';
      $upload_dir = wp_upload_dir();
      $temp_zip_file = $upload_dir['basedir'] . '/ip2nation.zip';

      //delete old file if exists
      unlink( $temp_zip_file );

      $result = wp_remote_get ($db_file_url, array( 'timeout' => $timeout ));

      //Writing of the ZIP db_file
      if ( !is_wp_error( $result ) ) {
        //Headers error check : 404
        if ( 200 != wp_remote_retrieve_response_code( $result ) ){
          $install_status = new WP_Error( 'http_404', trim( wp_remote_retrieve_response_message( $result ) ) );
        }

        // Save file to temp directory
        // ******To add a md5 routine : to check the integrity of the file
        $content = wp_remote_retrieve_body($result);
        $zip_size = file_put_contents ($temp_zip_file, $content);
        if (!$zip_size) { // writing error
          $install_status=__('Failure to save content locally, please try to re-install.','newstatpress');
        }
      }
      else { // WP_error
        $error_message = $result->get_error_message();
        echo '<div id="message" class="error"><p>' . $error_message . '</p></div>';
      }

      // require PclZip if not loaded
      if(! class_exists('PclZip')) {
        require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
      }

      // Unzip Db Archive
      $archive = new PclZip($temp_zip_file);
      $newstatpress_includes_path = WP_PLUGIN_DIR . '/' .dirname(plugin_basename(__FILE__)) . '/includes';
      if ($archive->extract(PCLZIP_OPT_PATH, $newstatpress_includes_path , PCLZIP_OPT_REMOVE_ALL_PATH) == 0) {
        $install_status=__('Failure to unzip archive, please try to re-install','newstatpress');
      }
      else {
        $install_status=__('Instalation of IP2nation database was successful','newstatpress');
      }

      // Remove Zip file
      unlink( $temp_zip_file );
      return $install_status;
  }


function iriNewStatPressExport() {
?>
<!--TODO chab, check if the input format is ok  -->
	<div class='wrap'><h2><?php _e('Export stats to text file','newstatpress'); ?> (csv)</h2>
    <p><?php _e('You should define the stats period you want to export:','newstatpress'); ?><p>
	<form method=get>
    <table>
      <tr>
        <td><?php _e('From:','newstatpress'); ?> </td>
        <td><input type=text size=10 maxlength=8 =from placeholder='<?php _e('YYYYMMDD','newstatpress');?>'></td>
      </tr>
      <tr>
        <td><?php _e('To:','newstatpress'); ?> </td>
        <td><input type=text size=10 maxlength=8 name=to placeholder='<?php _e('YYYYMMDD','newstatpress');?>'></td>
      </tr>
    </table>
    <table>
      <tr>
        <td><?php _e('You should choose a fields delimiter to separate the data:','newstatpress'); ?> </td>
        <td><select name=del>
          <option>,</option>
          <option>tab</option>
          <option>;</option>
          <option>|</option></select>
      </tr>
    </table>
    <input class='button button-primary' type=submit value=<?php _e('Export','newstatpress'); ?>>
    <input type=hidden name=page value=newstatpress><input type=hidden name=newstatpress_action value=exportnow>
</form>
	</div>
<?php
}

/**
 * Check and export if capability of user allow that
 */
function iri_checkExport(){
  if (isset($_GET['newstatpress_action']) && $_GET['newstatpress_action'] == 'exportnow') {
    $mincap=get_option('newstatpress_mincap');
    if ($mincap == '') $mincap = "level_8";
    if ( current_user_can( $mincap ) ) {
      iriNewStatPressExportNow();
    }
  }
}

/**
 * Export the NewStatPress data
 */
function iriNewStatPressExportNow() {
  global $wpdb;
  $table_name = $wpdb->prefix . "statpress";
  $filename=get_bloginfo('title' )."-newstatpress_".$_GET['from']."-".$_GET['to'].".csv";
  header('Content-Description: File Transfer');
  header("Content-Disposition: attachment; filename=$filename");
  header('Content-Type: text/plain charset=' . get_option('blog_charset'), true);
  $qry = $wpdb->get_results(
    "SELECT *
     FROM $table_name
     WHERE
       date>='".(date("Ymd",strtotime(substr($_GET['from'],0,8))))."' AND
       date<='".(date("Ymd",strtotime(substr($_GET['to'],0,8))))."';
    ");
  $del=substr($_GET['del'],0,1);
  if ($del=="t") {
    $del="\t";
  }
  print "date".$del."time".$del."ip".$del."urlrequested".$del."agent".$del."referrer".$del."search".$del."nation".$del."os".$del."browser".$del."searchengine".$del."spider".$del."feed\n";
  foreach ($qry as $rk) {
    print '"'.$rk->date.'"'.$del.'"'.$rk->time.'"'.$del.'"'.$rk->ip.'"'.$del.'"'.$rk->urlrequested.'"'.$del.'"'.$rk->agent.'"'.$del.'"'.$rk->referrer.'"'.$del.'"'.$rk->search.'"'.$del.'"'.$rk->nation.'"'.$del.'"'.$rk->os.'"'.$del.'"'.$rk->browser.'"'.$del.'"'.$rk->searchengine.'"'.$del.'"'.$rk->spider.'"'.$del.'"'.$rk->feed.'"'."\n";
  }
  die();
}

/**
 * Show overwiew
 */
function iriNewStatPressMain() {
  global $wpdb;
  $table_name = $wpdb->prefix . "statpress";

  iriOverview();

  $_newstatpress_url=PluginUrl();

  // determine the structure to use for URL
  $permalink_structure = get_settings('permalink_structure');
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
    print "<td>". irihdate($fivesdraft->date) ."</td>";
    print "<td>". $fivesdraft->time ."</td>";
    print "<td>". $fivesdraft->ip ."</td>";
    print "<td>". $fivesdraft->nation ."</td>";
    print "<td>". iri_NewStatPress_Abbrevia(iri_NewStatPress_Decode($fivesdraft->urlrequested),30) ."</td>";
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
    print "<tr><td>".irihdate($rk->date)."</td><td>".$rk->time."</td><td><a href='".$rk->referrer."' target='_blank'>".$rk->search."</a></td><td>".$rk->searchengine."</td><td><a href='".get_bloginfo('url').$extra.$rk->urlrequested."' target='_blank'>". __('page viewed','newstatpress'). "</a></td></tr>\n";
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
    print "<tr><td>".irihdate($rk->date)."</td><td>".$rk->time."</td><td><a href='".$rk->referrer."' target='_blank'>".iri_NewStatPress_Abbrevia($rk->referrer,80)."</a></td><td><a href='".get_bloginfo('url').$extra.$rk->urlrequested."'  target='_blank'>". __('page viewed','newstatpress'). "</a></td></tr>\n";
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
    print "<tr><td>".irihdate($rk->date)."</td><td>".$rk->time."</td><td>".iri_NewStatPress_Abbrevia(iri_NewStatPress_Decode($rk->urlrequested),60)."</td>";
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
    print "<tr><td>".irihdate($rk->date)."</td><td>".$rk->time."</td>";
    if($rk->spider != '') {
      $img=str_replace(" ","_",strtolower($rk->spider)).".png";
      print "<td><IMG class='img_os' SRC='".$_newstatpress_url."/images/spider/$img'> </td>";
    } else print "<td></td>";
    print "<td>".$rk->spider."</td><td> ".$rk->agent."</td></tr>\n";
  }
  print "</table></div>";

  print "<br />";
  print "&nbsp;<i>StatPress table size: <b>".iritablesize($wpdb->prefix . "statpress")."</b></i><br />";
  print "&nbsp;<i>StatPress current time: <b>".current_time('mysql')."</b></i><br />";
  print "&nbsp;<i>RSS2 url: <b>".get_bloginfo('rss2_url').' ('.iriNewStatPress_extractfeedreq(get_bloginfo('rss2_url')).")</b></i><br />";
}

/**
 * Extract the feed from the given url
 *
 * @param url the url to parse
 * @return the extracted url
 */
function iriNewStatPress_extractfeedreq($url) {
  list($null,$q)=explode("?",$url);
  if (strpos($q, "&")!== false) list($res,$null)=explode("&",$q);
  else $res=$q;
  return $res;
}

function iriNewStatPressDetails() {
  global $wpdb;
  $table_name = $wpdb->prefix . "statpress";

  //$querylimit="LIMIT 10";

  # Top days
  iriValueTable2("date", __('Top days','newstatpress') ,(get_option('newstatpress_el_top_days')=='') ? 5:get_option('newstatpress_el_top_days'));

  # O.S.
  iriValueTable2("os",__('OSes','newstatpress') ,(get_option('newstatpress_el_os')=='') ? 10:get_option('newstatpress_el_os'),"","","AND feed='' AND spider='' AND os<>''");

  # Browser
  iriValueTable2("browser",__('Browsers','newstatpress') ,(get_option('newstatpress_el_browser')=='') ? 10:get_option('newstatpress_el_browser'),"","","AND feed='' AND spider='' AND browser<>''");

  # Feeds
  iriValueTable2("feed",__('Feeds','newstatpress') ,(get_option('newstatpress_el_feed')=='') ? 5:get_option('newstatpress_el_feed'),"","","AND feed<>''");

  # SE
  iriValueTable2("searchengine",__('Search engines','newstatpress') ,(get_option('newstatpress_el_searchengine')=='') ? 10:get_option('newstatpress_el_searchengine'),"","","AND searchengine<>''");

  # Search terms
  iriValueTable2("search",__('Top search terms','newstatpress') ,(get_option('newstatpress_el_search')=='') ? 20:get_option('newstatpress_el_search'),"","","AND search<>''");

  # Top referrer
  iriValueTable2("referrer",__('Top referrers','newstatpress') ,(get_option('newstatpress_el_referrer')=='') ? 10:get_option('newstatpress_el_referrer'),"","","AND referrer<>'' AND referrer NOT LIKE '%".get_bloginfo('url')."%'");

  # Languages
  iriValueTable2("nation",__('Countries','newstatpress').'/'.__('Languages','newstatpress') ,(get_option('newstatpress_el_languages')=='') ? 20:get_option('newstatpress_el_languages'),"","","AND nation<>'' AND spider=''");

  # Spider
  iriValueTable2("spider",__('Spiders','newstatpress') ,(get_option('newstatpress_el_spiders')=='') ? 10:get_option('newstatpress_el_spiders'),"","","AND spider<>''");

  # Top Pages
  iriValueTable2("urlrequested",__('Top pages','newstatpress') ,(get_option('newstatpress_el_pages')=='') ? 5:get_option('newstatpress_el_pages'),"","urlrequested","AND feed='' and spider=''");

  # Top Days - Unique visitors
  iriValueTable2("date",__('Top days','newstatpress').' - '.__('Unique visitors','newstatpress') ,(get_option('newstatpress_el_visitors')=='') ? 5:get_option('newstatpress_el_visitors'),"distinct","ip","AND feed='' and spider=''"); /* Maddler 04112007: required patching iriValueTable */

  # Top Days - Pageviews
  iriValueTable2("date",__('Top days','newstatpress').' - '.__('Pageviews','newstatpress'),(get_option('newstatpress_el_daypages')=='') ? 5:get_option('newstatpress_el_daypages'),"","urlrequested","AND feed='' and spider=''"); /* Maddler 04112007: required patching iriValueTable */

  # Top IPs - Pageviews
  iriValueTable2("ip",__('Top IPs','newstatpress').' - '.__('Pageviews','newstatpress'),(get_option('newstatpress_el_ippages')=='') ? 5:get_option('newstatpress_el_ippages'),"","urlrequested","AND feed='' and spider=''"); /* Maddler 04112007: required patching iriValueTable */
}


/**
 * Converte da data us to default format di Wordpress
 *
 * @param dt the date to convert
 * @return converted data
 */
function newstatpress_hdate($dt = "00000000") {
  return mysql2date(get_option('date_format'), my_substr($dt, 0, 4) . "-" . my_substr($dt, 4, 2) . "-" . my_substr($dt, 6, 2));
}

/**
 * Decode the url in a better manner
 */
function newstatpress_Decode($out_url) {
  if(!iriNewStatPressPermalinksEnabled()) {
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
 */
function iriNewStatPressPermalinksEnabled() {
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
 * Display links for group of pages
 *
 * @param NP the group of pages
 * @param pp the page to show
 * @param action the action
 */
function newstatpress_print_pp_link($NP,$pp,$action) {
  // For all pages ($NP) Display first 3 pages, 3 pages before current page($pp), 3 pages after current page , each 25 pages and the 3 last pages for($action)
  $GUIL1 = FALSE;
  $GUIL2 = FALSE;// suspension points  not writed  style='border:0px;width:16px;height:16px;   style="border:0px;width:16px;height:16px;"
  if ($NP >1) {
    print "<font size='1'>".__('period of days','newstatpress')." : </font>";
    for ($i = 1; $i <= $NP; $i++) {
      if ($i <= $NP) {
        // $page is not the last page
        if($i == $pp) echo " [{$i}] "; // $page is current page
        else {
          // Not the current page Hyperlink them
          if (($i <= 3) or (($i >= $pp-3) and ($i <= $pp+3)) or ($i >= $NP-3) or is_int($i/100)) {
            echo '<a href="?page=newstatpress/newstatpress.php&newstatpress_action='.$action.'&pp=' . $i .'">' . $i . '</a> ';
          } else {
              if (($GUIL1 == FALSE) OR ($i==$pp+4)) {
                echo "...";
                $GUIL1 = TRUE;
              }
              if ($i == $pp-4) echo "..";
              if (is_int(($i-1)/100)) echo ".";
              if ($i == $NP-4) echo "..";
              // suspension points writed
            }
         }
      }
    }
  }
}

/**
 * Display links for group of pages
 *
 * @param NP the group of pages
 * @param pp the page to show
 * @param action the action
 * @param NA group
 * @param pa current page
 */
function newstatpress_print_pp_pa_link($NP,$pp,$action,$NA,$pa) {
  if ($NP<>0) newstatpress_print_pp_link($NP,$pp,$action);

  // For all pages ($NP) display first 5 pages, 3 pages before current page($pa), 3 pages after current page , 3 last pages
  $GUIL1 = FALSE;// suspension points not writed
  $GUIL2 = FALSE;

  echo '<table width="100%" border="0"><tr></tr></table>';
  if ($NA >1 ) {
    echo "<font size='1'>".__('Pages','newstatpress')." : </font>";
    for ($j = 1; $j <= $NA; $j++) {
      if ($j <= $NA) {  // $i is not the last Articles page
        if($j == $pa)  // $i is current page
          echo " [{$j}] ";
        else { // Not the current page Hyperlink them
          if (($j <= 5) or (( $j>=$pa-2) and ($j <= $pa+2)) or ($j >= $NA-2))
            echo '<a href="?page=newstatpress/newstatpress.php&newstatpress_action='.$action.'&pp=' . $pp . '&pa='. $j . '">' . $j . '</a> ';
          else {
            if ($GUIL1 == FALSE) echo "... "; $GUIL1 = TRUE;
            if (($j == $pa+4) and ($GUIL2 == FALSE)) {
              echo " ... ";
              $GUIL2 = TRUE;
            }
            // suspension points writed
          }
        }
      }
    }
  }
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
 * Get page post taken in statprss-visitors
 */
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

/**
 * New spy function taken in statpress-visitors
 */
function iriNewStatPressNewSpy() {
  global $wpdb;
  global $newstatpress_dir;
  $action="newspy";
  $table_name = $wpdb->prefix . "statpress";

  // number of IP or bot by page
  $LIMIT = get_option('newstatpress_ip_per_page_newspy');
  $LIMIT_PROOF = get_option('newstatpress_visits_per_ip_newspy');
  if ($LIMIT == 0) $LIMIT = 20;
  if ($LIMIT_PROOF == 0) $LIMIT_PROOF = 20;

  $pp = newstatpress_page_periode();

  // Number of distinct ip (unique visitors)
  $NumIP = $wpdb->get_var("
    SELECT count(distinct ip)
    FROM $table_name
    WHERE spider=''"
  );
  $NP = ceil($NumIP/$LIMIT);
  $LimitValue = ($pp * $LIMIT) - $LIMIT;

  $sql = "
    SELECT *
    FROM $table_name as T1
    JOIN
      (SELECT max(id) as MaxId,min(id) as MinId,ip, nation
       FROM $table_name
       WHERE spider=''
       GROUP BY ip
       ORDER BY MaxId
       DESC LIMIT $LimitValue, $LIMIT ) as T2
    ON T1.ip = T2.ip
    WHERE id BETWEEN MinId AND MaxId
    ORDER BY MaxId DESC, id DESC
  ";

  $qry = $wpdb->get_results($sql);

  echo "<div class='wrap'><h2>" . __('Visitors', 'newstatpress') . "</h2>";
?>
<script>
function ttogle(thediv){
if (document.getElementById(thediv).style.display=="inline") {
document.getElementById(thediv).style.display="none"
} else {document.getElementById(thediv).style.display="inline"}
}
</script>
<?php
  $ip = 0;
  $num_row=0;
  echo'<div id="paginating" align="center">';
  newstatpress_print_pp_link($NP,$pp,$action);
  echo'</div><table id="mainspytab" name="mainspytab" width="99%" border="0" cellspacing="0" cellpadding="4">';
  foreach ($qry as $rk) {
    // Visitors
    if ($ip <> $rk->ip) {
      //this is the first time these ip appear, print informations
      echo "<tr><td colspan='2' bgcolor='#dedede'><div align='left'>";
      $title='';
      $id ='';
      ///if ($rk->country <> '') {
      ///  $img=strtolower($rk->country).".png";
      ///  $lines = file(ABSPATH.'wp-content/plugins/'.dirname(dirname(dirname(plugin_basename(__FILE__)))) .'/def/domain.dat');
      ///  foreach($lines as $line_num => $country) {
      ///    list($id,$title)=explode("|",$country);
      ///    if($id===strtolower($rk->country)) break;
      ///  }
      ///  echo "http country <IMG class='img_os' alt='".$title."' title='".$title."' SRC='" .plugins_url('newstatpress/images/domain/'.$img, dirname(dirname(dirname(__FILE__)))). "'>  ";
      ///} else
        if($rk->nation <> '') {
          // the nation exist
          $img=strtolower($rk->nation).".png";
          $lines = file($newstatpress_dir.'/def/domain.dat');
          foreach($lines as $line_num => $nation) {
            list($id,$title)=explode("|",$nation);
            if($id===$rk->nation) break;
          }
          print "".__('Http domain', 'newstatpress')." <IMG class='img_os' alt='".$title."' title='".$title."' SRC='" .plugins_url('newstatpress/images/domain/'.$img, dirname(plugin_basename(__FILE__))). "'>  ";

        } else {
            $ch = curl_init('http://api.hostip.info/country.php?ip='.$rk->ip);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            $output .=".png";
            $output = strtolower($output);
            curl_close($ch);
            print "".__('Hostip country','newstatpress'). "<IMG style='border:0px;width:18;height:12px;' alt='".$title."' title='".$title."' SRC='" .plugins_url('newstatpress/images/domain/'.$output, dirname(plugin_basename(__FILE__))). "'>  ";
      }

        print "<strong><span><font size='2' color='#7b7b7b'>".$rk->ip."</font></span></strong> ";
        print "<span style='color:#006dca;cursor:pointer;border-bottom:1px dotted #AFD5F9;font-size:8pt;' onClick=ttogle('".$rk->ip."');>".__('more info','newstatpress')."</span></div>";
        print "<div id='".$rk->ip."' name='".$rk->ip."'>";

        if(get_option('newstatpress_cryptip')!='checked') {
          print "<br><iframe style='overflow:hidden;border:0px;width:100%;height:60px;font-family:helvetica;padding:0;' scrolling='no' marginwidth=0 marginheight=0 src=http://api.hostip.info/get_html.php?ip=".$rk->ip."></iframe>";
        }
        print "<br><small><span style='font-weight:700;'>OS or device:</span> ".$rk->os."</small>";
        print "<br><small><span style='font-weight:700;'>DNS Name:</span> ".gethostbyaddr($rk->ip)."</small>";
        print "<br><small><span style='font-weight:700;'>Browser:</span> ".$rk->browser."</small>";
        print "<br><small><span style='font-weight:700;'>Browser Detail:</span> ".$rk->agent."</small>";
        print "<br><br></div>";
        print "<script>document.getElementById('".$rk->ip."').style.display='none';</script>";
        print "</td></tr>";


        echo "<td valign='top' width='151'><div><font size='1' color='#3B3B3B'><strong>" . newstatpress_hdate($rk->date) . " " . $rk->time . "</strong></font></div></td>
              <td>" . newstatpress_Decode($rk->urlrequested) ."";
        if ($rk->searchengine != '') print "<br><small>".__('arrived from','newstatpress')." <b>" . $rk->searchengine . "</b> ".__('searching','newstatpress')." <a href='" . $rk->referrer . "' target=_blank>" . urldecode($rk->search) . "</a></small>";
        elseif ($rk->referrer != '' && strpos($rk->referrer, get_option('home')) === false) print "<br><small>".__('arrived from','newstatpress')." <a href='" . $rk->referrer . "' target=_blank>" . $rk->referrer . "</a></small>";
        echo "</div></td></tr>\n";
        $ip=$rk->ip;
        $num_row = 1;
    } elseif ($num_row < $LIMIT_PROOF) {
        echo "<tr><td valign='top' width='151'><div><font size='1' color='#3B3B3B'><strong>" . newstatpress_hdate($rk->date) . " " . $rk->time . "</strong></font></div></td>
              <td><div>" . newstatpress_Decode($rk->urlrequested) . "";
        if ($rk->searchengine != '') print "<br><small>".__('arrived from','newstatpress')." <b>" . $rk->searchengine . "</b> ".__('searching','newstatpress')." <a href='" . $rk->referrer . "' target=_blank>" . urldecode($rk->search) . "</a></small>";
        elseif ($rk->referrer != '' && strpos($rk->referrer, get_option('home')) === false) print "<br><small>".__('arrived from','newstatpress')." <a href='" . $rk->referrer . "' target=_blank>" . $rk->referrer . "</a></small>";
        $num_row += 1;
        echo "</div></td></tr>\n";
      }
   }
   echo "</div></td></tr>\n</table>";
   newstatpress_print_pp_link($NP,$pp,$action);
   echo "</div>";
}

/**
 * New spy bot function taken in statpress-visitors
 */
function iriNewStatPressSpyBot() {
  global $wpdb;
  global $newstatpress_dir;

  $action="spybot";
  $table_name = $wpdb->prefix . "statpress";

  $LIMIT = get_option('newstatpress_bot_per_page_spybot');
  $LIMIT_PROOF = get_option('newstatpress_visits_per_bot_spybot');

  if ($LIMIT ==0) $LIMIT = 10;
  if ($LIMIT_PROOF == 0) $LIMIT_PROOF = 30;

  $pa = newstatpress_page_posts();
  $LimitValue = ($pa * $LIMIT) - $LIMIT;

  // limit the search 7 days ago
  $day_ago = gmdate('Ymd', current_time('timestamp') - 7*86400);
  $MinId = $wpdb->get_var("
    SELECT min(id) as MinId
    FROM $table_name
    WHERE date > $day_ago
  ");

  // Number of distinct spiders after $day_ago
  $Num = $wpdb->get_var("
    SELECT count(distinct spider)
    FROM $table_name
    WHERE
      spider<>'' AND
      id >$MinId
  ");
  $NA = ceil($Num/$LIMIT);

  echo "<div class='wrap'><h2>" . __('Spy Bot', 'newstatpress') . "</h2>";

  // selection of spider, group by spider, order by most recently visit (last id in the table)
  $sql = "
    SELECT *
    FROM $table_name as T1
    JOIN
    (SELECT spider,max(id) as MaxId
     FROM $table_name
     WHERE spider<>''
     GROUP BY spider
     ORDER BY MaxId
     DESC LIMIT $LimitValue, $LIMIT
    ) as T2
    ON T1.spider = T2.spider
    WHERE T1.id > $MinId
    ORDER BY MaxId DESC, id DESC
  ";
  $qry = $wpdb->get_results($sql);

  echo '<div align="center">';
  newstatpress_print_pp_pa_link (0,0,$action,$NA,$pa);
  echo '</div><div align="left">';
?>
<script>
function ttogle(thediv){
if (document.getElementById(thediv).style.display=="inline") {
document.getElementById(thediv).style.display="none"
} else {document.getElementById(thediv).style.display="inline"}
}
</script>
<table id="mainspytab" name="mainspytab" width="99%" border="0" cellspacing="0" cellpadding="4"><div align='left'>
<?php
  $spider="robot";
  $num_row=0;
  foreach ($qry as $rk) {  // Bot Spy
    if ($robot <> $rk->spider) {
      echo "<div align='left'>
            <tr>
            <td colspan='2' bgcolor='#dedede'>";
      $img=str_replace(" ","_",strtolower($rk->spider));
      $img=str_replace('.','',$img).".png";
      $lines = file($newstatpress_dir.'/def/spider.dat');
      foreach($lines as $line_num => $spider) { //seeks the tooltip corresponding to the photo
        list($title,$id)=explode("|",$spider);
        if($title==$rk->spider) break; // break, the tooltip ($title) is found
      }
      echo "<IMG class='img_os' style='align:left;' alt='".$title."' title='".$title."' SRC='" .plugins_url('newstatpress/images/spider/'.$img, dirname(plugin_basename(__FILE__))). "'>
            <span style='color:#006dca;cursor:pointer;border-bottom:1px dotted #AFD5F9;font-size:8pt;' onClick=ttogle('" . $img . "');>http more info</span>
            <div id='" . $img . "' name='" . $img . "'><br /><small>" . $rk->ip . "</small><br><small>" . $rk->agent . "<br /></small></div>
            <script>document.getElementById('" . $img . "').style.display='none';</script>
            </tr>
            <tr><td valign='top' width='170'><div><font size='1' color='#3B3B3B'><strong>" . newstatpress_hdate($rk->date) . " " . $rk->time . "</strong></font></div></td>
            <td><div>" . newstatpress_Decode($rk->urlrequested) . "</div></td></tr>";
      $robot=$rk->spider;
      $num_row=1;
    } elseif ($num_row < $LIMIT_PROOF) {
        echo "<tr>
              <td valign='top' width='170'><div><font size='1' color='#3B3B3B'><strong>" . newstatpress_hdate($rk->date) . " " . $rk->time . "</strong></font></div></td>
              <td><div>" . newstatpress_Decode($rk->urlrequested) . "</div></td></tr>";
        $num_row+=1;
      }
      echo "</div></td></tr>\n";
  }
  echo "</table>";
  newstatpress_print_pp_pa_link (0,0,$action,$NA,$pa);
  echo "</div>";
}

/**
 * Newstatpress spy function
 */
function iriNewStatPressSpy() {
  global $wpdb;
  global $newstatpress_dir;

  $table_name = $wpdb->prefix . "statpress";

  # Spy
  $today = gmdate('Ymd', current_time('timestamp'));
  $yesterday = gmdate('Ymd', current_time('timestamp')-86400);
  print "<div class='wrap'><h2>".__('Last visitors','newstatpress')."</h2>";
  $sql="
    SELECT ip,nation,os,browser,agent
    FROM $table_name
    WHERE
      spider='' AND
      feed='' AND
      date BETWEEN '$yesterday' AND '$today'
    GROUP BY ip ORDER BY id DESC LIMIT 20";
  $qry = $wpdb->get_results($sql);

?>
<script>
function ttogle(thediv){
if (document.getElementById(thediv).style.display=="inline") {
document.getElementById(thediv).style.display="none"
} else {document.getElementById(thediv).style.display="inline"}
}
</script>
<div>
<table id="mainspytab" name="mainspytab" width="99%" border="0" cellspacing="0" cellpadding="4">
<?php
  foreach ($qry as $rk) {
    print "<tr><td colspan='2' bgcolor='#dedede'><div align='left'>";

    if($rk->nation <> '') {
      // the nation exist
      $img=strtolower($rk->nation).".png";
      $lines = file($newstatpress_dir.'/def/domain.dat');
      foreach($lines as $line_num => $nation) {
        list($id,$title)=explode("|",$nation);
        if($id===$rk->nation) break;
      }
      echo "<IMG class='img_os' alt='".$title."' title='".$title."' SRC='" .plugins_url('newstatpress/images/domain/'.$img, dirname(plugin_basename(__FILE__))). "'>  ";
    } else {
        $ch = curl_init('http://api.hostip.info/country.php?ip='.$rk->ip);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $output .=".png";
        $output = strtolower($output);
        curl_close($ch);
        echo "<IMG style='border:0px;width:18;height:12px;' alt='".$title."' title='".$title."' SRC='" .plugins_url('newstatpress/images/domain/'.$output, dirname(plugin_basename(__FILE__))). "'>  ";
      }


    print " <strong><span><font size='2' color='#7b7b7b'>".$rk->ip."</font></span></strong> ";
    print "<span style='color:#006dca;cursor:pointer;border-bottom:1px dotted #AFD5F9;font-size:8pt;' onClick=ttogle('".$rk->ip."');>".__('more info','newstatpress')."</span></div>";
    print "<div id='".$rk->ip."' name='".$rk->ip."'>";
    if(get_option('newstatpress_cryptip')!='checked') {
      print "<br><iframe style='overflow:hidden;border:0px;width:100%;height:60px;font-family:helvetica;padding:0;' scrolling='no' marginwidth=0 marginheight=0 src=http://api.hostip.info/get_html.php?ip=".$rk->ip."></iframe>";
    }
    print "<br><small><span style='font-weight:700;'>OS or device:</span> ".$rk->os."</small>";
    print "<br><small><span style='font-weight:700;'>DNS Name:</span> ".gethostbyaddr($rk->ip)."</small>";
    print "<br><small><span style='font-weight:700;'>Browser:</span> ".$rk->browser."</small>";
    print "<br><small><span style='font-weight:700;'>Browser Detail:</span> ".$rk->agent."</small>";
    print "<br><br></div>";
    print "<script>document.getElementById('".$rk->ip."').style.display='none';</script>";
    print "</td></tr>";
    $qry2=$wpdb->get_results("
      SELECT *
      FROM $table_name
      WHERE
        ip='".$rk->ip."' AND
        (date BETWEEN '$yesterday' AND '$today')
      ORDER BY id
      LIMIT 10"
    );
    foreach ($qry2 as $details) {
      print "<tr>";
      print "<td valign='top' width='151'><div><font size='1' color='#3B3B3B'><strong>".irihdate($details->date)." ".$details->time."</strong></font></div></td>";
      print "<td><div><a href='".get_bloginfo('url')."/?".$details->urlrequested."' target='_blank'>".iri_NewStatPress_Decode($details->urlrequested)."</a>";
      if($details->searchengine != '') {
        print "<br><small>".__('arrived from','newstatpress')." <b>".$details->searchengine."</b> ".__('searching','newstatpress')." <a href='".$details->referrer."' target='_blank'>".$details->search."</a></small>";
      } elseif($details->referrer != '' && strpos($details->referrer,get_option('home'))===FALSE) {
          print "<br><small>".__('arrived from','newstatpress')." <a href='".$details->referrer."' target='_blank'>".$details->referrer."</a></small>";
        }
      print "</div></td>";
      print "</tr>\n";
    }
  }
?>
</table>
</div>
<?php
}


/**
 * Check if the argument is an IP addresses
 *
 * @param ip the ip to check
 * @return TRUE if it is an ip
 */
function iri_CheckIP($ip) {
  return ( ! preg_match( "/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $ip)) ? FALSE : TRUE;
}

function iriNewStatPressSearch($what='') {
  global $wpdb;
  $table_name = $wpdb->prefix . "statpress";

  $f['urlrequested']=__('URL Requested','newstatpress');
  $f['agent']=__('Agent','newstatpress');
  $f['referrer']=__('Referrer','newstatpress');
  $f['search']=__('Search terms','newstatpress');
  $f['searchengine']=__('Search engine','newstatpress');
  $f['os']=__('Operative system','newstatpress');
  $f['browser']=__('Browser','newstatpress');
  $f['spider']=__('Spider','newstatpress');
  $f['ip']=__('IP','newstatpress');
?>
  <div class='wrap'><h2><?php _e('Search','newstatpress'); ?></h2>
  <form method=get><table>
  <?php
    for($i=1;$i<=3;$i++) {
      print "<tr>";
      print "<td>".__('Field','newstatpress')." <select name=where$i><option value=''></option>";
      foreach ( array_keys($f) as $k ) {
        print "<option value='$k'";
        if($_GET["where$i"] == $k) { print " SELECTED "; }
        print ">".$f[$k]."</option>";
      }
      print "</select></td>";
      if (isset($_GET["groupby$i"])) print "<td><input type=checkbox name=groupby$i value='checked' ".$_GET["groupby$i"]."> ".__('Group by','newstatpress')."</td>";
      else print "<td><input type=checkbox name=groupby$i value='checked' "."> ".__('Group by','newstatpress')."</td>";

      if (isset($_GET["sortby$i"])) print "<td><input type=checkbox name=sortby$i value='checked' ".$_GET["sortby$i"]."> ".__('Sort by','newstatpress')."</td>";
      else print "<td><input type=checkbox name=sortby$i value='checked' "."> ".__('Sort by','newstatpress')."</td>";

      print "<td>, ".__('if contains','newstatpress')." <input type=text name=what$i value='".$_GET["what$i"]."'></td>";
      print "</tr>";
    }
?>
  </table>
  <br>
  <table>
   <tr>
     <td>
       <table>
         <tr><td><input type=checkbox name=oderbycount value=checked <?php print $_GET['oderbycount'] ?>> <?php _e('sort by count if grouped','newstatpress'); ?></td></tr>
         <tr><td><input type=checkbox name=spider value=checked <?php print $_GET['spider'] ?>> <?php _e('include spiders/crawlers/bot','newstatpress'); ?></td></tr>
         <tr><td><input type=checkbox name=feed value=checked <?php print $_GET['feed'] ?>> <?php _e('include feed','newstatpress'); ?></td></tr>
       </table>
     </td>
     <td width=15> </td>
     <td>
       <table>
         <tr>
           <td><?php _e('Limit results to','newstatpress'); ?>
             <select name=limitquery><?php if($_GET['limitquery'] >0) { print "<option>".$_GET['limitquery']."</option>";} ?><option>1</option><option>5</option><option>10</option><option>20</option><option>50</option></select>
           </td>
         </tr>
         <tr><td>&nbsp;</td></tr>
         <tr>
          <td align=right><input type=submit value=<?php _e('Search','newstatpress'); ?> name=searchsubmit></td>
         </tr>
       </table>
     </td>
    </tr>
   </table>
   <input type=hidden name=page value='newstatpress/newstatpress.php'><input type=hidden name=newstatpress_action value=search>
  </form><br>
<?php

 if(isset($_GET['searchsubmit'])) {
   # query builder
   $qry="";
   # FIELDS
   $fields="";
   for($i=1;$i<=3;$i++) {
     if($_GET["where$i"] != '') {
       $fields.=$_GET["where$i"].",";
     }
   }
   $fields=rtrim($fields,",");
   # WHERE
   $where="WHERE 1=1";

   if (!isset($_GET['spider'])) { $where.=" AND spider=''"; }
   else if($_GET['spider'] != 'checked') { $where.=" AND spider=''"; }

   if (!isset($_GET['feed'])) { $where.=" AND feed=''"; }
   else if($_GET['feed'] != 'checked') { $where.=" AND feed=''"; }

   for($i=1;$i<=3;$i++) {
     if(($_GET["what$i"] != '') && ($_GET["where$i"] != '')) {
       $where.=" AND ".$_GET["where$i"]." LIKE '%".$_GET["what$i"]."%'";
     }
   }
   # ORDER BY
   $orderby="";
   for($i=1;$i<=3;$i++) {
     if (isset($_GET["sortby$i"]) && ($_GET["sortby$i"] == 'checked') && ($_GET["where$i"] != '')) {
       $orderby.=$_GET["where$i"].',';
     }
   }

   # GROUP BY
   $groupby="";
   for($i=1;$i<=3;$i++) {
     if(isset($_GET["groupby$i"]) && ($_GET["groupby$i"] == 'checked') && ($_GET["where$i"] != '')) {
       $groupby.=$_GET["where$i"].',';
     }
   }
   if($groupby != '') {
     $groupby="GROUP BY ".rtrim($groupby,',');
     $fields.=",count(*) as totale";
     if(isset($_GET["oderbycount"]) && $_GET['oderbycount'] == 'checked') { $orderby="totale DESC,".$orderby; }
   }

   if($orderby != '') { $orderby="ORDER BY ".rtrim($orderby,','); }

   $limit="LIMIT ".$_GET['limitquery'];

   # Results
   print "<h2>".__('Results','newstatpress')."</h2>";
   $sql="SELECT $fields FROM $table_name $where $groupby $orderby $limit;";
   //print "$sql<br>";
   print "<table class='widefat'><thead><tr>";
   for($i=1;$i<=3;$i++) {
     if($_GET["where$i"] != '') { print "<th scope='col'>".ucfirst($_GET["where$i"])."</th>"; }
   }
   if($groupby != '') { print "<th scope='col'>".__('Count','newstatpress')."</th>"; }
     print "</tr></thead><tbody id='the-list'>";
     $qry=$wpdb->get_results($sql,ARRAY_N);
     foreach ($qry as $rk) {
       print "<tr>";
       for($i=1;$i<=3;$i++) {
         print "<td>";
         if($_GET["where$i"] == 'urlrequested') { print iri_NewStatPress_Decode($rk[$i-1]); }
         else { if(isset($rk[$i-1])) print $rk[$i-1]; }
         print "</td>";
       }
         print "</tr>";
     }
     print "</table>";
     print "<br /><br /><font size=1 color=gray>sql: $sql</font></div>";
  }
}

/**
 * Abbreviate the given string to a fixed length
 *
 * @param s the string
 * @param c the numebr of chars
 * @return the abbreviate string
 */
function iri_NewStatPress_Abbrevia($s,$c) {
  $s=__($s);
  $res=""; if(strlen($s)>$c) { $res="..."; }
  return substr($s,0,$c).$res;
}

/**
 * Decode the given url
 *
 * @param out_url the given url to decode
 * @return the decoded url
 */
function iri_NewStatPress_Decode($out_url) {
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


function iri_NewStatPress_URL() {
  $urlRequested = (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '' );
  if ( $urlRequested == "" ) { // SEO problem!
    $urlRequested = (isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : '' );
  }
  if(substr($urlRequested,0,2) == '/?') { $urlRequested=substr($urlRequested,2); }
  if($urlRequested == '/') { $urlRequested=''; }
  return $urlRequested;
}


# Converte da data us to default format di Wordpress
function irihdate($dt = "00000000") {
  return mysql2date(get_option('date_format'), substr($dt,0,4)."-".substr($dt,4,2)."-".substr($dt,6,2));
}


function iritablesize($table) {
  global $wpdb;
  $res = $wpdb->get_results("SHOW TABLE STATUS LIKE '$table'");
  foreach ($res as $fstatus) {
    $data_lenght = $fstatus->Data_length;
    $data_rows = $fstatus->Rows;
  }
  return number_format(($data_lenght/1024/1024), 2, ",", " ")." Mb ($data_rows ". __('records','newstatpress').")";
}

function iriindextablesize($table) {
  global $wpdb;
  $res = $wpdb->get_results("SHOW TABLE STATUS LIKE '$table'");
  foreach ($res as $fstatus) {
    $index_lenght = $fstatus->Index_length;
  }
  return number_format(($index_lenght/1024/1024), 2, ",", " ")." Mb";
}

/**
 * Get google url query for geo data
 *
 * @param data_array the array of data_array
 * @return the url with data
 */
function iriGetGoogleGeo($data_array) {
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
function iriGetGooglePie($title, $data_array) {
  if(empty($data_array)) { return ''; }
  // get hash
  foreach($data_array as $key => $value ) {
    $values[] = $value;
    $labels[] = $key;
  }

  return "?title=".$title."&chd=".(implode(",",$values))."&chl=".urlencode(implode("|",$labels));
}

function iriValueTable2($fld,$fldtitle,$limit = 0,$param = "", $queryfld = "", $exclude= "", $print = TRUE) {
  global $wpdb;
  $table_name = $wpdb->prefix . "statpress";

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
      if($fld == 'date') { $rk->$fld = irihdate($rk->$fld); }
      if($fld == 'urlrequested') { $rk->$fld = iri_NewStatPress_Decode($rk->$fld); }
      $data[substr($rk->$fld,0,250)]=$rk->pageview;
    }
  }

  // Draw table body
  $text .= "<tbody id='the-list'>";
  if($rks > 0) {  // Chart!

    if($fld == 'nation') { // Nation chart
      $charts=plugins_url('newstatpress')."/includes/geocharts.html".iriGetGoogleGeo($data);
    }
    else { // Pie chart
      $charts=plugins_url('newstatpress')."/includes/piecharts.html".iriGetGooglePie($fldtitle, $data);
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


function iriGetLanguage($accepted) {
  return substr($accepted,0,2);
}


function iriGetQueryPairs($url){
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
 */
function iriGetOS($arg) {
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
 */
function iriGetBrowser($arg) {
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
function iriCheckBanIP($arg){
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
function iriGetSE($referrer = null){
  global $newstatpress_dir;

  $key = null;
  $lines = file($newstatpress_dir.'/def/searchengines.dat');
  foreach($lines as $line_num => $se) {
    list($nome,$url,$key)=explode("|",$se);
    if(strpos($referrer,$url)===FALSE) continue;

    # find if
    $variables = iriGetQueryPairs(html_entity_decode($referrer));
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
 */
function iriGetSpider($agent = null){
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
function iri_NewStatPress_lastmonth() {
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
 */
 function iri_NewStatPress_CreateTable() {
   global $wpdb;
   global $wp_db_version;
   $table_name = $wpdb->prefix . "statpress";

   // Add by chab
   // If the database is already created then DROP INDEX for update
///   if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) { $list_index_to_drop=['spider_nation','agent','ip_date','search','os','browser','referrer','feed_spider_os','date_feed_spider', 'feed_spider_browser'];
///     foreach ($list_index_to_drop as $i)
///     {
///       $sql_createtable = "ALTER TABLE $table_name DROP INDEX $i";
///       $wpdb->query($sql_createtable);
///     }
///   }

  $sql_createtable = "
    CREATE TABLE " . $table_name . " (
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
      UNIQUE KEY id (id),
      INDEX spider_nation (spider, nation),
      INDEX ip_date (ip, date),
      INDEX agent (agent),
      index search (search),
      index referrer (referrer),
      index feed_spider_os (feed, spider, os),
      index os (os),
      index date_feed_spider (date, feed, spider),
      index feed_spider_browser (feed, spider, browser),
      index browser (browser)
    );";
  if($wp_db_version >= 5540) $page = 'wp-admin/includes/upgrade.php';
  else $page = 'wp-admin/upgrade'.'-functions.php';

  require_once(ABSPATH . $page);
  dbDelta($sql_createtable);
}

/**
 * Get if this is a feed
 *
 * @param url the url to test
 * @return the kind of feed that is fount
 */
function iri_NewStatPress_is_feed($url) {
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
 */
function iriStatAppend() {
  global $wpdb;
  $table_name = $wpdb->prefix . "statpress";
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
  if(iriCheckBanIP($ipAddress) == '') { return ''; }

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
  $urlRequested=iri_NewStatPress_URL();
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
  $userAgent = (isset($_SERVER['HTTP_USER_AGENT']) ? htmlentities($_SERVER['HTTP_USER_AGENT']) : '');
  $userAgent=esc_sql($userAgent);
  $spider=iriGetSpider($userAgent);

  if(($spider != '') and (get_option('newstatpress_donotcollectspider')=='checked')) { return ''; }

  if($spider != '') {
    $os=''; $browser='';
  } else {
      // Trap feeds
      $feed=iri_NewStatPress_is_feed(get_bloginfo('url').$_SERVER['REQUEST_URI']);
      // Get OS and browser
      $os=iriGetOS($userAgent);
      $browser=iriGetBrowser($userAgent);

     $exp_referrer=iriGetSE($referrer);
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
    $countrylang=iriGetLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE']);
  }

  // Auto-delete visits if...
  if(get_option('newstatpress_autodelete') != '') {
    $t=gmdate("Ymd",strtotime('-'.get_option('newstatpress_autodelete')));
    $results =$wpdb->query( "DELETE FROM " . $table_name . " WHERE date < '" . $t . "'");
  }
  // Auto-delete spiders visits if...
  if(get_option('newstatpress_autodelete_spiders') != '') {
    $t=gmdate("Ymd",strtotime('-'.get_option('newstatpress_autodelete_spiders')));
    $results =$wpdb->query(
       "DELETE FROM " . $table_name . "
        WHERE date < '" . $t . "' and
              feed='' and
              spider<>''
       ");
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
      iri_NewStatPress_CreateTable();
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


/**
 * Get the days a user has choice for updating the database
 *
 * @return the number of days of -1 for all days
 */
function iriNewStatPressDays() {

  // get the number of days for the update
  switch (get_option('newstatpress_updateint')) {
    case '1 week':
      $days=7; break;
    case '2 weeks':
      $days=14; break;
    case '3 weeks':
      $days=21; break;
    case '1 month':
      $days=30; break;
    case '2 months':
      $days=60; break;
    case '3 months':
      $days=90; break;
    case '6 months':
      $days=180; break;
    case '9 months':
      $days=270; break;
    case '12 months':
      $days=365; break;
    default :
      $days=-1; // infinite in the past, for all day
  }

  return $days;
}

/**
 * Performes database update with new definitions
 */
function iriNewStatPressUpdate() {
  global $wpdb;
  global $newstatpress_dir;

  $table_name = $wpdb->prefix . "statpress";

  $wpdb->flush();     // flush for counting right the queries
  $start_time = microtime(true);

  $days=iriNewStatPressDays();  // get the number of days for the update

  $to_date  = gmdate("Ymd",current_time('timestamp'));

  if ($days==-1) $from_date= "19990101";   // use a date where this plugin was not present
  else $from_date = gmdate('Ymd', current_time('timestamp')-86400*$days);

  $_newstatpress_url=PluginUrl();

  $wpdb->show_errors();

  //add by chab
  //$var requesting the absolute path
  $img_ok = $_newstatpress_url.'images/ok.gif';
  $ip2nation_db = $newstatpress_dir.'/includes/ip2nation.sql';

  print "<div class='wrap'><h2>".__('Database Update','newstatpress')."</h2><br />";

  print "<table class='widefat nsp'><thead><tr><th scope='col'>".__('Updating...','newstatpress')."</th><th scope='col' style='width:400px;'>".__('Size','newstatpress')."</th><th scope='col' style='width:100px;'>".__('Result','newstatpress')."</th><th></th></tr></thead>";
  print "<tbody id='the-list'>";

  # check if ip2nation .sql file exists
  if(file_exists($ip2nation_db)) {
    print "<tr><td>ip2nation.sql</td>";
    $FP = fopen ($ip2nation_db, 'r' );
    $READ = fread ( $FP, filesize ($ip2nation_db) );
    $READ = explode ( ";\n", $READ );
    foreach ( $READ as $RED ) {
      if($RES != '') { $wpdb->query($RED); }
    }
    print "<td>".iritablesize("ip2nation")."</td>";
    print "<td><img class'update_img' src='$img_ok'></td></tr>";
  }

  # update table
  iri_NewStatPress_CreateTable();

  print "<tr><td>". __('Structure','newstatpress'). " $table_name</td>";
  print "<td>".iritablesize($wpdb->prefix."statpress")."</td>";
  print "<td><img class'update_img' src='$img_ok'></td></tr>";

  print "<tr><td>". __('Index','newstatpress'). " $table_name</td>";
  print "<td>".iriindextablesize($wpdb->prefix."statpress")."</td>";
  print "<td><img class'update_img' src='$img_ok'></td></tr>";

  # Update Feed
  print "<tr><td>". __('Feeds','newstatpress'). "</td>";
  $wpdb->query("
    UPDATE $table_name
    SET feed=''
    WHERE date BETWEEN $from_date AND $to_date;"
  );

  # not standard
  $wpdb->query("
    UPDATE $table_name
    SET feed='RSS2'
    WHERE
      urlrequested LIKE '%/feed/%' AND
      date BETWEEN $from_date AND $to_date;"
  );

  $wpdb->query("
    UPDATE $table_name
    SET feed='RSS2'
    WHERE
      urlrequested LIKE '%wp-feed.php%' AND
      date BETWEEN $from_date AND $to_date;"
  );

  # standard blog info urls
  $s=iriNewStatPress_extractfeedreq(get_bloginfo('comments_atom_url'));
  if($s != '') {
    $wpdb->query("
      UPDATE $table_name
      SET feed='COMMENT'
      WHERE
        INSTR(urlrequested,'$s')>0 AND
        date BETWEEN $from_date AND $to_date;"
    );
  }
  $s=iriNewStatPress_extractfeedreq(get_bloginfo('comments_rss2_url'));
  if($s != '') {
    $wpdb->query("
      UPDATE $table_name
      SET feed='COMMENT'
      WHERE
        INSTR(urlrequested,'$s')>0 AND
        date BETWEEN $from_date AND $to_date;"
    );
  }
  $s=iriNewStatPress_extractfeedreq(get_bloginfo('atom_url'));
  if($s != '') {
    $wpdb->query("
      UPDATE $table_name
      SET feed='ATOM'
      WHERE
        INSTR(urlrequested,'$s')>0 AND
        date BETWEEN $from_date AND $to_date;"
    );
  }
  $s=iriNewStatPress_extractfeedreq(get_bloginfo('rdf_url'));
  if($s != '') {
    $wpdb->query("
      UPDATE $table_name
      SET feed='RDF'
      WHERE
        INSTR(urlrequested,'$s')>0 AND
        date BETWEEN $from_date AND $to_date;"
    );
  }
  $s=iriNewStatPress_extractfeedreq(get_bloginfo('rss_url'));
  if($s != '') {
    $wpdb->query("
      UPDATE $table_name
      SET feed='RSS'
      WHERE
        INSTR(urlrequested,'$s')>0 AND
        date BETWEEN $from_date AND $to_date;"
    );
  }
  $s=iriNewStatPress_extractfeedreq(get_bloginfo('rss2_url'));
  if($s != '') {
    $wpdb->query("
      UPDATE $table_name
      SET feed='RSS2'
      WHERE
        INSTR(urlrequested,'$s')>0 AND
        date BETWEEN $from_date AND $to_date;"
    );
  }

  $wpdb->query("
    UPDATE $table_name
    SET feed = ''
    WHERE
      isnull(feed) AND
      date BETWEEN $from_date AND $to_date;"
   );

  print "<td></td>";
  print "<td><img class'update_img' src='$img_ok'></td></tr>";

  # Update OS
  print "<tr><td>". __('OSes','newstatpress'). "</td>";
  $wpdb->query("
    UPDATE $table_name
    SET os = ''
    WHERE date BETWEEN $from_date AND $to_date;"
  );
  $lines = file($newstatpress_dir.'/def/os.dat');
  foreach($lines as $line_num => $os) {
    list($nome_os,$id_os)=explode("|",$os);
    $qry="
      UPDATE $table_name
      SET os = '$nome_os'
      WHERE
        os='' AND
        replace(agent,' ','') LIKE '%".$id_os."%' AND
        date BETWEEN $from_date AND $to_date;";
    $wpdb->query($qry);
  }
  print "<td></td>";
  print "<td><img class'update_img' src='$img_ok'></td></tr>";


  # Update Browser
  print "<tr><td>". __('Browsers','newstatpress'). "</td>";
  $wpdb->query("
    UPDATE $table_name
    SET browser = ''
    WHERE date BETWEEN $from_date AND $to_date;"
  );
  $lines = file($newstatpress_dir.'/def/browser.dat');
  foreach($lines as $line_num => $browser) {
    list($nome,$id)=explode("|",$browser);
    $qry="
      UPDATE $table_name
      SET browser = '$nome'
      WHERE
        browser='' AND
        replace(agent,' ','') LIKE '%".$id."%' AND
        date BETWEEN $from_date AND $to_date;";
    $wpdb->query($qry);
  }
  print "<td></td>";
  print "<td><img class'update_img' src='$img_ok'></td></tr>";


  # Update Spider
  print "<tr><td>". __('Spiders','newstatpress'). "</td>";
  $wpdb->query("
    UPDATE $table_name
    SET spider = ''
    WHERE date BETWEEN $from_date AND $to_date;"
  );
  $lines = file($newstatpress_dir.'/def/spider.dat');
  foreach($lines as $line_num => $spider) {
    list($nome,$id)=explode("|",$spider);
    $qry="
      UPDATE $table_name
      SET spider = '$nome',os='',browser=''
      WHERE
        spider='' AND
        replace(agent,' ','') LIKE '%".$id."%' AND
        date BETWEEN $from_date AND $to_date;";
    $wpdb->query($qry);
  }
  print "<td></td>";
  print "<td><img class'update_img' src='$img_ok'></td></tr>";


  # Update Search engine
  print "<tr><td>". __('Search engines','newstatpress'). " </td>";
  $wpdb->query("
    UPDATE $table_name
    SET searchengine = '', search=''
    WHERE date BETWEEN $from_date AND $to_date;");
  $qry = $wpdb->get_results("
    SELECT id, referrer
    FROM $table_name
    WHERE
      length(referrer)!=0 AND
      date BETWEEN $from_date AND $to_date");
  foreach ($qry as $rk) {
    list($searchengine,$search_phrase)=explode("|",iriGetSE($rk->referrer));
    if($searchengine <> '') {
      $q="
        UPDATE $table_name
        SET searchengine = '$searchengine', search='".addslashes($search_phrase)."'
        WHERE
          id=".$rk->id." AND
          date BETWEEN $from_date AND $to_date;";
      $wpdb->query($q);
    }
  }
  print "<td></td>";
  print "<td><img class'update_img' src='$img_ok'></td></tr>";

  $end_time = microtime(true);
  $sql_queries=$wpdb->num_queries;

  # Final statistics
  print "<tr><td>". __('Final Structure','newstatpress'). " $table_name</td>";
  print "<td>".iritablesize($wpdb->prefix."statpress")."</td>";
  print "<td><img class'update_img' src='$img_ok'></td></tr>";

  print "<tr><td>". __('Final Index','newstatpress'). " $table_name</td>";
  print "<td>".iriindextablesize($wpdb->prefix."statpress")."</td>";
  print "<td><img class'update_img' src='$img_ok'></td></tr>";

  print "<tr><td>". __('Duration of the update','newstatpress'). "</td>";
  print "<td>".round($end_time - $start_time, 2)." sec</td>";
  print "<td><img class'update_img' src='$img_ok'></td></tr>";

  print "<tr><td>". __('This update was done in','newstatpress'). "</td>";
  print "<td>".$sql_queries." " . __('SQL queries','newstatpress'). "</td>";
  print "<td><img class'update_img' src='$img_ok'></td></tr>";

  print "</tbody></table></div><br>\n";
  $wpdb->hide_errors();
}


function NewStatPress_Widget($w='') {

}

/**
 * Return the expanded vars into the give code. Wrapper for internal use
 */
function NewStatPress_Print($body='') {
  return iri_NewStatPress_Vars($body);
}


/**
 * Expand vars into the give code
 *
 * @param boby the code where to look for variables to expand
 * @return the modified code
 */
function iri_NewStatPress_Vars($body) {
  global $wpdb;
  $table_name = $wpdb->prefix . "statpress";

  # look for %visits%
  if(strpos(strtolower($body),"%visits%") !== FALSE) {
    $qry = $wpdb->get_results(
      "SELECT count(DISTINCT(ip)) AS pageview
       FROM $table_name
       WHERE
        date = '".gmdate("Ymd",current_time('timestamp'))."' AND
        spider='' and feed='';
      ");
    $body = str_replace("%visits%", $qry[0]->pageview, $body);
  }

  # look for %yvisits%
  if(strpos(strtolower($body),"%yvisits%") !== FALSE) {
    $qry = $wpdb->get_results(
      "SELECT count(DISTINCT(ip)) AS pageview
       FROM $table_name
       WHERE
        date = '".gmdate("Ymd",current_time('timestamp')-86400)."' AND
        spider='' and feed='';
      ");
    $body = str_replace("%yvisits%", $qry[0]->pageview, $body);
  }

  # look for %mvisits%
  if(strpos(strtolower($body),"%mvisits%") !== FALSE) {
    $qry = $wpdb->get_results(
      "SELECT count(DISTINCT(ip)) AS pageview
       FROM $table_name
       WHERE
        date LIKE '".gmdate('Ym', current_time('timestamp'))."%'
        spider='' and feed='';
      ");
    $body = str_replace("%mvisits%", $qry[0]->pageview, $body);
  }

  # look for %totalvisits%
  if(strpos(strtolower($body),"%totalvisits%") !== FALSE) {
    $qry = $wpdb->get_results(
      "SELECT count(DISTINCT(ip)) AS pageview
       FROM $table_name
       WHERE
         spider='' AND
         feed='';
      ");
    $body = str_replace("%totalvisits%", $qry[0]->pageview, $body);
  }

  # look for %totalpageviews%
  if(strpos(strtolower($body),"%totalpageviews%") !== FALSE) {
    $qry = $wpdb->get_results(
      "SELECT count(id) AS pageview
       FROM $table_name
       WHERE
         spider='' AND
         feed='';
      ");
    $body = str_replace("%totalpageviews%", $qry[0]->pageview, $body);
  }

  # look for %todaytotalpageviews%
  if(strpos(strtolower($body),"%todaytotalpageviews%") !== FALSE) {
    $qry = $wpdb->get_results(
      "SELECT count(id) AS pageview
       FROM $table_name
       WHERE
         date = '".gmdate("Ymd",current_time('timestamp'))."' AND
         spider='' AND
         feed='';
      ");
    $body = str_replace("%todaytotalpageviews%", $qry[0]->pageview, $body);
  }

  # look for %thistotalvisits%
  if(strpos(strtolower($body),"%thistotalvisits%") !== FALSE) {
    $qry = $wpdb->get_results(
      "SELECT count(DISTINCT(ip)) AS pageview
       FROM $table_name
       WHERE
         spider='' AND
         feed='' AND
         urlrequested='".iri_NewStatPress_URL()."';
      ");
    $body = str_replace("%thistotalvisits%", $qry[0]->pageview, $body);
  }

  # look for %alltotalvisits%
  if(strpos(strtolower($body),"%alltotalvisits%") !== FALSE) {
    $qry = $wpdb->get_results(
      //"SELECT SUM(pageview) AS pageview
      // FROM (
      //   SELECT count(DISTINCT(ip)) AS pageview
      //   FROM $table_name AS t1
      //   WHERE
      //     spider='' AND
      //     feed='' AND
      //     urlrequested!=''
      //   GROUP BY urlrequested
      // ) AS t2;
      //");
      "SELECT count(distinct urlrequested, ip) AS pageview
       FROM $table_name AS t1
       WHERE
         spider='' AND
         feed='' AND
         urlrequested!='';
      ");
    $body = str_replace("%alltotalvisits%", $qry[0]->pageview, $body);
  }

  # look for %since%
  if(strpos(strtolower($body),"%since%") !== FALSE) {
    $qry = $wpdb->get_results(
      "SELECT date
       FROM $table_name
       ORDER BY date
       LIMIT 1;
      ");
    $body = str_replace("%since%", irihdate($qry[0]->date), $body);
  }

  # look for %os%
  if(strpos(strtolower($body),"%os%") !== FALSE) {
    $userAgent = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
    $os=iriGetOS($userAgent);
    $body = str_replace("%os%", $os, $body);
  }

  # look for %browser%
  if(strpos(strtolower($body),"%browser%") !== FALSE) {
    $browser=iriGetBrowser($userAgent);
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
    $body = str_replace("%toppost%", iri_NewStatPress_Decode($qry[0]->urlrequested), $body);
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
    $body = str_replace("%topbrowser%", iri_NewStatPress_Decode($qry[0]->browser), $body);
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
    $body = str_replace("%topos%", iri_NewStatPress_Decode($qry[0]->os), $body);
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
    $body = str_replace("%topsearch%", iri_NewStatPress_Decode($qry[0]->search), $body);
  }

  # look for %installed%
  if(strpos(strtolower($body),"%installed%") !== FALSE) {
    $body = str_replace("%installed%", new_count_total(), $body);
  }
  return $body;
}

/**
 * Get top posts
 *
 * @param limit the number of post to show
 * @param showcounts if checked show totals
 * @return result of extraction
 */
function iri_NewStatPress_TopPosts($limit=5, $showcounts='checked') {
  global $wpdb;
  $res="\n<ul>\n";
  $table_name = $wpdb->prefix . "statpress";
  $qry = $wpdb->get_results(
    "SELECT urlrequested,count(*) as totale
     FROM $table_name
     WHERE
       spider='' AND
       feed='' AND
       urlrequested LIKE '%p=%'
     GROUP BY urlrequested
     ORDER BY totale DESC LIMIT $limit;
    ");

  foreach ($qry as $rk) {
    $res.="<li><a href='?".$rk->urlrequested."' target='_blank'>".iri_NewStatPress_Decode($rk->urlrequested)."</a></li>\n";
    if(strtolower($showcounts) == 'checked') { $res.=" (".$rk->totale.")"; }
  }
  return "$res</ul>\n";
}


function widget_newstatpress_init($args) {
  if ( !function_exists('wp_register_sidebar_widget') || !function_exists('wp_register_widget_control') ) return;

  // Multifunctional StatPress pluging
  function widget_newstatpress_control() {
    $options = get_option('widget_newstatpress');
    if ( !is_array($options) ) $options = array('title'=>'NewStatPress', 'body'=>'Visits today: %visits%');
    if ( isset($_POST['newstatpress-submit']) && $_POST['newstatpress-submit'] ) {
      $options['title'] = strip_tags(stripslashes($_POST['newstatpress-title']));
      $options['body'] = stripslashes($_POST['newstatpress-body']);
      update_option('widget_newstatpress', $options);
    }
    $title = htmlspecialchars($options['title'], ENT_QUOTES);
    $body = htmlspecialchars($options['body'], ENT_QUOTES);
     // the form
    echo '<p style="text-align:right;"><label for="newstatpress-title">' . __('Title:', 'newstatpress') . ' <input style="width: 250px;" id="newstatpress-title" name="newstatpress-title" type="text" value="'.$title.'" /></label></p>';
    echo '<p style="text-align:right;"><label for="newstatpress-body"><div>' . __('Body:', 'newstatpress') . '</div><textarea style="width: 288px;height:100px;" id="newstatpress-body" name="newstatpress-body" type="textarea">'.$body.'</textarea></label></p>';
    echo '<input type="hidden" id="newstatpress-submit" name="newstatpress-submit" value="1" /><div style="font-size:7pt;">%totalvisits% %visits% %thistotalvisits% %os% %browser% %ip% %since% %visitorsonline% %usersonline% %toppost% %topbrowser% %topos%</div>';
  }
  function widget_newstatpress($args) {
    extract($args);
    $options = get_option('widget_newstatpress');
    $title = $options['title'];
    $body = $options['body'];
    echo $before_widget;
    print($before_title . $title . $after_title);
    print iri_NewStatPress_Vars($body);
    echo $after_widget;
  }
  wp_register_sidebar_widget('NewStatPress', 'NewStatPress', 'widget_newstatpress');
  wp_register_widget_control('NewStatPress', array('NewStatPress','widgets'), 'widget_newstatpress_control', 300, 210);

  // Top posts
  function widget_newstatpresstopposts_control() {
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
    echo '<p style="text-align:right;"><label for="newstatpresstopposts-title">' . __('Title','newstatpress') . ' <input style="width: 250px;" id="newstatpress-title" name="newstatpresstopposts-title" type="text" value="'.$title.'" /></label></p>';
    echo '<p style="text-align:right;"><label for="newstatpresstopposts-howmany">' . __('Limit results to','newstatpress') . ' <input style="width: 100px;" id="newstatpresstopposts-howmany" name="newstatpresstopposts-howmany" type="text" value="'.$howmany.'" /></label></p>';
    echo '<p style="text-align:right;"><label for="newstatpresstopposts-showcounts">' . __('Visits','newstatpress') . ' <input id="newstatpresstopposts-showcounts" name="newstatpresstopposts-showcounts" type=checkbox value="checked" '.$showcounts.' /></label></p>';
    echo '<input type="hidden" id="newstatpress-submitTopPosts" name="newstatpresstopposts-submit" value="1" />';
  }
  function widget_newstatpresstopposts($args) {
    extract($args);
    $options = get_option('widget_newstatpresstopposts');
    $title = htmlspecialchars($options['title'], ENT_QUOTES);
    $howmany = htmlspecialchars($options['howmany'], ENT_QUOTES);
    $showcounts = htmlspecialchars($options['showcounts'], ENT_QUOTES);
    echo $before_widget;
    print($before_title . $title . $after_title);
    print iri_NewStatPress_TopPosts($howmany,$showcounts);
    echo $after_widget;
  }
  wp_register_sidebar_widget('NewStatPress TopPosts', 'NewStatPress TopPosts', 'widget_newstatpresstopposts');
  wp_register_widget_control('NewStatPress TopPosts', array('NewStatPress TopPosts','widgets'), 'widget_newstatpresstopposts_control', 300, 110);
}

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
 */
function content_newstatpress($content = '') {
  ob_start();
  $TYPEs = array();
  $TYPE = preg_match_all('/\[NewStatPress: (.*)\]/Ui', $content, $TYPEs);

  foreach ($TYPEs[1] as $k => $TYPE) {
    switch ($TYPE) {
      case "Overview":
        $replacement=iriOverview(FALSE);
        break;
      case "Top days":
        $replacement=iriValueTable2("date","Top days", (get_option('newstatpress_el_top_days')=='') ? 5:get_option('newstatpress_el_top_days'), FALSE);
        break;
      case "O.S.":
        $replacement=iriValueTable2("os","O.S.",(get_option('newstatpress_el_os')=='') ? 10:get_option('newstatpress_el_os'),"","","AND feed='' AND spider='' AND os<>''", FALSE);
        break;
      case "Browser":
        $replacement=iriValueTable2("browser","Browser",(get_option('newstatpress_el_browser')=='') ? 10:get_option('newstatpress_el_browser'),"","","AND feed='' AND spider='' AND browser<>''", FALSE);
        break;
      case "Feeds":
        $replacement=iriValueTable2("feed","Feeds",(get_option('newstatpress_el_feed')=='') ? 5:get_option('newstatpress_el_feed'),"","","AND feed<>''", FALSE);
        break;
      case "Search Engine":
        $replacement=iriValueTable2("searchengine","Search engines",(get_option('newstatpress_el_searchengine')=='') ? 10:get_option('newstatpress_el_searchengine'),"","","AND searchengine<>''", FALSE);
        break;
      case "Search terms":
        $replacement=iriValueTable2("search","Top search terms",(get_option('newstatpress_el_search')=='') ? 20:get_option('newstatpress_el_search'),"","","AND search<>''", FALSE);
        break;
      case "Top referrer":
        $replacement= iriValueTable2("referrer","Top referrer",(get_option('newstatpress_el_referrer')=='') ? 10:get_option('newstatpress_el_referrer'),"","","AND referrer<>'' AND referrer NOT LIKE '%".get_bloginfo('url')."%'", FALSE);
        break;
      case "Languages":
        $replacement=iriValueTable2("nation","Countries/Languages",(get_option('newstatpress_el_languages')=='') ? 20:get_option('newstatpress_el_languages'),"","","AND nation<>'' AND spider=''", FALSE);
        break;
      case "Spider":
        $replacement=iriValueTable2("spider","Spiders",(get_option('newstatpress_el_spiders')=='') ? 10:get_option('newstatpress_el_spiders'),"","","AND spider<>''", FALSE);
        break;
      case "Top Pages":
        $replacement=iriValueTable2("urlrequested","Top pages",(get_option('newstatpress_el_pages')=='') ? 5:get_option('newstatpress_el_pages'),"","urlrequested","AND feed='' and spider=''", FALSE);
        break;
      case "Top Days - Unique visitors":
        $replacement=iriValueTable2("date","Top Days - Unique visitors",(get_option('newstatpress_el_visitors')=='') ? 5:get_option('newstatpress_el_visitors'),"distinct","ip","AND feed='' and spider=''", FALSE);
        break;
      case "Top Days - Pageviews":
        $replacement=iriValueTable2("date","Top Days - Pageviews",(get_option('newstatpress_el_daypages')=='') ? 5:get_option('newstatpress_el_daypages'),"","urlrequested","AND feed='' and spider=''", FALSE);
        break;
      case "Top IPs - Pageviews":
        $replacement=iriValueTable2("ip","Top IPs - Pageviews",(get_option('newstatpress_el_ippages')=='') ? 5:get_option('newstatpress_el_ippages'),"","urlrequested","AND feed='' and spider=''", FALSE);
        break;
      default:
        $replacement="";
    }
    $content = str_replace($TYPEs[0][$k], $replacement, $content);
  }
  ob_get_clean();
  return $content;
}


/**
 * Show statistics in dashboard
 */
function iri_dashboard_widget_function() {
  global $wpdb;
  $table_name = $wpdb->prefix . "statpress";

  # Tabella OVERVIEW
  $unique_color="#114477";
  $web_color="#3377B6";
  $rss_color="#f38f36";
  $spider_color="#83b4d8";
  $lastmonth = iri_NewStatPress_lastmonth();
  $thismonth = gmdate('Ym', current_time('timestamp'));
  $yesterday = gmdate('Ymd', current_time('timestamp')-86400);
  $today = gmdate('Ymd', current_time('timestamp'));
  $tlm[0]=substr($lastmonth,0,4); $tlm[1]=substr($lastmonth,4,2);

  //print "<div class='wrap'><h2>". __('Overview','NewStatPress'). "</h2>";
  print "<table class='widefat'><thead><tr>
  <th scope='col'></th>
  <th scope='col'>". __('Total since','newstatpress'). "<br /><font size=1>";
  print NewStatPress_Print('%since%');
  print "</font></th>
  <th scope='col'>". __('Last month','newstatpress'). "<br /><font size=1>" . gmdate('M, Y',gmmktime(0,0,0,$tlm[1],1,$tlm[0])) ."</font></th>
  <th scope='col'>". __('This month','newstatpress'). "<br /><font size=1>" . gmdate('M, Y', current_time('timestamp')) ."</font></th>
  <th scope='col'>". __('Target This month','newstatpress'). "<br /><font size=1>" . gmdate('M, Y', current_time('timestamp')) ."</font></th>
  <th scope='col'>". __('Yesterday','newstatpress'). "<br /><font size=1>" . gmdate('d M, Y', current_time('timestamp')-86400) ."</font></th>
  <th scope='col'>". __('Today','newstatpress'). "<br /><font size=1>" . gmdate('d M, Y', current_time('timestamp')) ."</font></th>
  </tr></thead>
  <tbody id='the-list'>";
  ################################################################################################
  # VISITORS ROW
  print "<tr><td><div style='background:$unique_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>". __('Visitors','newstatpress'). "</td>";

  #TOTAL
  $qry_total = $wpdb->get_row("
    SELECT count(DISTINCT ip) AS visitors
    FROM $table_name
    WHERE
      feed='' AND
      spider=''
  ");
  print "<td>" . $qry_total->visitors . "</td>\n";

  #LAST MONTH
  $qry_lmonth = $wpdb->get_row("
   SELECT count(DISTINCT ip) AS visitors
   FROM $table_name
   WHERE
     feed='' AND
     spider='' AND
     date LIKE '" . $lastmonth . "%'
  ");
  print "<td>" . $qry_lmonth->visitors . "</td>\n";

  #THIS MONTH
  $qry_tmonth = $wpdb->get_row("
    SELECT count(DISTINCT ip) AS visitors
    FROM $table_name
    WHERE
      feed='' AND
      spider='' AND
      date LIKE '" . $thismonth . "%'
  ");
  $qry_tmonth->change = null;
  $qry_tmonth->added = null;
  if($qry_lmonth->visitors <> 0) {
    $pc = round( 100 * ($qry_tmonth->visitors / $qry_lmonth->visitors ) - 100,1);
    if($pc >= 0) $pc = "+" . $pc;
    $qry_tmonth->change = "<code> (" . $pc . "%)</code>";
  }
  print "<td>" . $qry_tmonth->visitors . $qry_tmonth->change . "</td>\n";

  #TARGET
  $qry_tmonth->target = round($qry_tmonth->visitors / (
    (date("d", current_time('timestamp')) - 1 +
    (date("H", current_time('timestamp')) +
    (date("i", current_time('timestamp')) + 1)/ 60.0) / 24.0)) * date("t", current_time('timestamp'))
  );

  if($qry_lmonth->visitors <> 0) {
    $pt = round( 100 * ($qry_tmonth->target / $qry_lmonth->visitors ) - 100,1);
    if($pt >= 0) $pt = "+" . $pt;
    $qry_tmonth->added = "<code> (" . $pt . "%)</code>";
  }
  print "<td>" . $qry_tmonth->target . $qry_tmonth->added . "</td>\n";

  #YESTERDAY
  $qry_y = $wpdb->get_row("
    SELECT count(DISTINCT ip) AS visitors
    FROM $table_name
    WHERE
      feed='' AND
      spider='' AND
      date = '$yesterday'
  ");
  print "<td>" . $qry_y->visitors . "</td>\n";

  #TODAY
  $qry_t = $wpdb->get_row("
    SELECT count(DISTINCT ip) AS visitors
    FROM $table_name
    WHERE
      feed='' AND
      spider='' AND
      date = '$today'
  ");
  print "<td>" . $qry_t->visitors . "</td>\n";
  print "</tr>";

  ################################################################################################
  # PAGEVIEWS ROW
  print "<tr><td><div style='background:$web_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>". __('Pageviews','newstatpress'). "</td>";

  #TOTAL
  $qry_total = $wpdb->get_row("
    SELECT count(date) as pageview
    FROM $table_name
    WHERE
      feed='' AND
      spider=''
  ");
  print "<td>" . $qry_total->pageview . "</td>\n";

  #LAST MONTH
  $prec=0;
  $qry_lmonth = $wpdb->get_row("
    SELECT count(date) as pageview
    FROM $table_name
    WHERE
      feed='' AND
      spider='' AND
      date LIKE '" . $lastmonth . "%'
  ");
  print "<td>".$qry_lmonth->pageview."</td>\n";

  #THIS MONTH
  $qry_tmonth = $wpdb->get_row("
    SELECT count(date) as pageview
    FROM $table_name
    WHERE
      feed='' AND
      spider='' AND
      date LIKE '" . $thismonth . "%'
  ");
  $qry_tmonth->change = null;
  $qry_tmonth->added = null;
  if($qry_lmonth->pageview <> 0) {
    $pc = round( 100 * ($qry_tmonth->pageview / $qry_lmonth->pageview ) - 100,1);
    if($pc >= 0) $pc = "+" . $pc;
    $qry_tmonth->change = "<code> (" . $pc . "%)</code>";
  }
  print "<td>" . $qry_tmonth->pageview . $qry_tmonth->change . "</td>\n";

  #TARGET
  $qry_tmonth->target = round($qry_tmonth->pageview / (
    (date("d", current_time('timestamp')) - 1 +
    (date("H", current_time('timestamp')) +
    (date("i", current_time('timestamp')) + 1)/ 60.0) / 24.0)) * date("t", current_time('timestamp'))
  );
  if($qry_lmonth->pageview <> 0) {
    $pt = round( 100 * ($qry_tmonth->target / $qry_lmonth->pageview ) - 100,1);
    if($pt >= 0) $pt = "+" . $pt;
      $qry_tmonth->added = "<code> (" . $pt . "%)</code>";
  }
  print "<td>" . $qry_tmonth->target . $qry_tmonth->added . "</td>\n";

  #YESTERDAY
  $qry_y = $wpdb->get_row("
    SELECT count(date) as pageview
    FROM $table_name
    WHERE
      feed='' AND
      spider='' AND
      date = '$yesterday'
  ");
  print "<td>" . $qry_y->pageview . "</td>\n";

  #TODAY
  $qry_t = $wpdb->get_row("
    SELECT count(date) as pageview
    FROM $table_name
    WHERE
      feed='' AND
      spider='' AND
      date = '$today'
  ");
  print "<td>" . $qry_t->pageview . "</td>\n";
  print "</tr>";

  ################################################################################################
  # SPIDERS ROW
  print "<tr><td><div style='background:$spider_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>". __('Spiders','newstatpress'). "</td>";
  #TOTAL
  $qry_total = $wpdb->get_row("
    SELECT count(date) as spiders
    FROM $table_name
    WHERE
      feed='' AND
      spider<>''
  ");
  print "<td>" . $qry_total->spiders . "</td>\n";

  #LAST MONTH
  $prec=0;
  $qry_lmonth = $wpdb->get_row("
    SELECT count(date) as spiders
    FROM $table_name
    WHERE
      feed='' AND
      spider<>'' AND
      date LIKE '" . $lastmonth . "%'
  ");
  print "<td>" . $qry_lmonth->spiders. "</td>\n";

  #THIS MONTH
  $prec=$qry_lmonth->spiders;
  $qry_tmonth = $wpdb->get_row("
    SELECT count(date) as spiders
    FROM $table_name
    WHERE
      feed='' AND
      spider<>'' AND
      date LIKE '" . $thismonth . "%'
  ");
  $qry_tmonth->change = null;
  $qry_tmonth->added = null;
  if($qry_lmonth->spiders <> 0) {
    $pc = round( 100 * ($qry_tmonth->spiders / $qry_lmonth->spiders ) - 100,1);
    if($pc >= 0) $pc = "+" . $pc;
    $qry_tmonth->change = "<code> (" . $pc . "%)</code>";
  }
  print "<td>" . $qry_tmonth->spiders . $qry_tmonth->change . "</td>\n";

  #TARGET
  $qry_tmonth->target = round($qry_tmonth->spiders / (
    (date("d", current_time('timestamp')) - 1 +
    (date("H", current_time('timestamp')) +
    (date("i", current_time('timestamp')) + 1)/ 60.0) / 24.0)) * date("t", current_time('timestamp'))
  );
  if($qry_lmonth->spiders <> 0) {
    $pt = round( 100 * ($qry_tmonth->target / $qry_lmonth->spiders ) - 100,1);
    if($pt >= 0) $pt = "+" . $pt;
    $qry_tmonth->added = "<code> (" . $pt . "%)</code>";
  }
  print "<td>" . $qry_tmonth->target . $qry_tmonth->added . "</td>\n";

  #YESTERDAY
  $qry_y = $wpdb->get_row("
    SELECT count(date) as spiders
    FROM $table_name
    WHERE
      feed='' AND
      spider<>'' AND
      date = '$yesterday'
  ");
  print "<td>" . $qry_y->spiders . "</td>\n";

  #TODAY
  $qry_t = $wpdb->get_row("
    SELECT count(date) as spiders
    FROM $table_name
    WHERE
      feed='' AND
      spider<>'' AND
      date = '$today'
  ");
  print "<td>" . $qry_t->spiders . "</td>\n";
  print "</tr>";

  ################################################################################################
  # FEEDS ROW
  print "<tr><td><div class='feeds-row'></div>". __('Feeds','newstatpress'). "</td>";
  #TOTAL
  $qry_total = $wpdb->get_row("SELECT count(date) as feeds FROM $table_name WHERE feed<>'' AND spider='' ");
  print "<td>".$qry_total->feeds."</td>\n";


  #LAST MONTH
  $qry_lmonth = $wpdb->get_row("
    SELECT count(date) as feeds
    FROM $table_name
    WHERE
      feed<>'' AND
      spider='' AND
      date LIKE '" . $lastmonth . "%'
  ");
  print "<td>".$qry_lmonth->feeds."</td>\n";

  #THIS MONTH
  $qry_tmonth = $wpdb->get_row("
   SELECT count(date) as feeds
   FROM $table_name
   WHERE
     feed<>'' AND
     spider='' AND
     date LIKE '" . $thismonth . "%'
  ");
  $qry_tmonth->change = null;
  $qry_tmonth->added = null;
  if($qry_lmonth->feeds <> 0) {
    $pc = round( 100 * ($qry_tmonth->feeds / $qry_lmonth->feeds ) - 100,1);
    if($pc >= 0) $pc = "+" . $pc;
    $qry_tmonth->change = "<code> (" . $pc . "%)</code>";
  }
  print "<td>" . $qry_tmonth->feeds . $qry_tmonth->change . "</td>\n";

  #TARGET
  $qry_tmonth->target = round($qry_tmonth->feeds / (
    (date("d", current_time('timestamp')) - 1 +
    (date("H", current_time('timestamp')) +
    (date("i", current_time('timestamp')) + 1)/ 60.0) / 24.0)) * date("t", current_time('timestamp'))
  );
  if($qry_lmonth->feeds <> 0) {
    $pt = round( 100 * ($qry_tmonth->target / $qry_lmonth->feeds ) - 100,1);
    if($pt >= 0) $pt = "+" . $pt;
    $qry_tmonth->added = "<code> (" . $pt . "%)</code>";
  }
  print "<td>" . $qry_tmonth->target . $qry_tmonth->added . "</td>\n";

  $qry_y = $wpdb->get_row("
    SELECT count(date) as feeds
    FROM $table_name
    WHERE
      feed<>'' AND
      spider='' AND
      date = '".$yesterday."'
  ");
  print "<td>".$qry_y->feeds."</td>\n";

  $qry_t = $wpdb->get_row("
    SELECT count(date) as feeds
    FROM $table_name
    WHERE
      feed<>'' AND
      spider='' AND
      date = '$today'
  ");
  print "<td>".$qry_t->feeds."</td>\n";
  print "</tr></table><br />\n\n";
  print '</tr></table>';
  print '</div>';
  # END OF OVERVIEW
  ####################################################################################################

  print "<div class='wrap'><h4><a href='admin.php?page=newstatpress/newstatpress.php'>". __('More details','newstatpress'). " &raquo;</a></h4>";
}

/**
 * Make the overwiew
 *
 * @param print true if to print
 * @return the printing represetnation if print is false
 */
function iriOverview($print = TRUE) {
  global $wpdb;
  $table_name = $wpdb->prefix . "statpress";

  $result='';
  # Tabella OVERVIEW
  // $unique_color="#114477";
  // $web_color="#3377B6";
  // $rss_color="#f38f36";
  // $spider_color="#83b4d8";

  $since = NewStatPress_Print('%since%');
  $lastmonth = iri_NewStatPress_lastmonth();
  $thismonth = gmdate('Ym', current_time('timestamp'));
  $yesterday = gmdate('Ymd', current_time('timestamp')-86400);
  $today = gmdate('Ymd', current_time('timestamp'));
  $tlm[0]=substr($lastmonth,0,4); $tlm[1]=substr($lastmonth,4,2);

  $lastmonthHeader = gmdate('M, Y',gmmktime(0,0,0,$tlm[1],1,$tlm[0]));
  $thismonthHeader = gmdate('M, Y', current_time('timestamp'));
  $yesterdayHeader = gmdate('d M', current_time('timestamp')-86400);
  $todayHeader = gmdate('d M', current_time('timestamp'));


  $result.="<div class='wrap'><h2>". __('Overview','newstatpress'). "</h2>
            <table class='widefat center nsp'>
            <thead><tr>
            <th></th>
            <th>". __('Total since','newstatpress'). "<span class='date-overview'> $since </span></th>
            <th scope='col'>". __('Last month','newstatpress'). "<span class='date-overview'> $lastmonthHeader </span></th>
            <th scope='col'>". __('This month','newstatpress'). "<span class='date-overview'> $thismonthHeader </span></th>
            <th scope='col'>". __('Target This month','newstatpress'). "<span class='date-overview'> $thismonthHeader </span></th>
            <th scope='col'>". __('Yesterday','newstatpress'). "<span class='date-overview'> $yesterdayHeader </span></th>
            <th scope='col'>". __('Today','newstatpress'). "<span class='date-overview'> $todayHeader </span></th>
            </tr></thead>
            <tbody id='the-list-overview'>";

  ################################################################################################
  # VISITORS ROW
  $result.="<tr><td class='test visitors'>". __('Visitors','newstatpress'). "</td>";

  #TOTAL
  $qry_total = $wpdb->get_row("SELECT count(DISTINCT ip) AS visitors FROM $table_name WHERE feed='' AND spider=''");
  $result.="<td>$qry_total->visitors</td>\n";

  #LAST MONTH
  $qry_lmonth = $wpdb->get_row("SELECT count(DISTINCT ip) AS visitors FROM $table_name WHERE
      feed='' AND
      spider='' AND
      date LIKE '" . $lastmonth . "%'
  ");
  $result.="<td>$qry_lmonth->visitors</td>\n";

  #THIS MONTH
  $qry_tmonth = $wpdb->get_row("
    SELECT count(DISTINCT ip) AS visitors
    FROM $table_name
    WHERE
      feed='' AND
      spider='' AND
      date LIKE '" . $thismonth . "%'
  ");
  $qry_tmonth->change = null;
  $qry_tmonth->added = null;
  if($qry_lmonth->visitors <> 0) {
    $pc = round( 100 * ($qry_tmonth->visitors / $qry_lmonth->visitors ) - 100,1);
    if($pc >= 0) $pc = "+" . $pc;
    $qry_tmonth->change = "<code> (" . $pc . "%)</code>";
  }
  $result = $result. "<td>" . $qry_tmonth->visitors . $qry_tmonth->change . "</td>\n";

  #TARGET
  $qry_tmonth->target = round($qry_tmonth->visitors / (
    (date("d", current_time('timestamp')) - 1 +
    (date("H", current_time('timestamp')) +
    (date("i", current_time('timestamp')) + 1)/ 60.0) / 24.0)) * date("t", current_time('timestamp'))
  );
  if($qry_lmonth->visitors <> 0) {
    $pt = round( 100 * ($qry_tmonth->target / $qry_lmonth->visitors ) - 100,1);
    if($pt >= 0) $pt = "+" . $pt;
      $qry_tmonth->added = "<code> (" . $pt . "%)</code>";
    }
  $result = $result. "<td>" . $qry_tmonth->target . $qry_tmonth->added . "</td>\n";

  #YESTERDAY
  $qry_y = $wpdb->get_row("
     SELECT count(DISTINCT ip) AS visitors
     FROM $table_name
     WHERE
       feed='' AND
       spider='' AND
       date = '$yesterday'
  ");
  $result.="<td>$qry_y->visitors</td>\n";

  #TODAY
  $qry_t = $wpdb->get_row("
    SELECT count(DISTINCT ip) AS visitors
    FROM $table_name
    WHERE
      feed='' AND
      spider='' AND
      date = '$today'
  ");
  $result = $result. "<td>" . $qry_t->visitors . "</td>\n";
  $result = $result. "</tr>";

  ################################################################################################
  # PAGEVIEWS ROW
  $result = $result. "<tr><td class='test pageviews'>". __('Pageviews','newstatpress'). "</td>"; // add by chab

  // $result = $result. "<tr><td><div style='background:$web_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>". __('Pageviews','newstatpress'). "</td>";

  #TOTAL
  $qry_total = $wpdb->get_row("
    SELECT count(date) as pageview
    FROM $table_name
    WHERE
      feed='' AND
      spider=''
  ");
  $result = $result. "<td>" . $qry_total->pageview . "</td>\n";

  #LAST MONTH
  $prec=0;
  $qry_lmonth = $wpdb->get_row("
    SELECT count(date) as pageview
    FROM $table_name
    WHERE
      feed='' AND
      spider='' AND
      date LIKE '" . $lastmonth . "%'
    ");
  $result = $result. "<td>".$qry_lmonth->pageview."</td>\n";

  #THIS MONTH
  $qry_tmonth = $wpdb->get_row("
    SELECT count(date) as pageview
    FROM $table_name
    WHERE
      feed='' AND
      spider='' AND
      date LIKE '" . $thismonth . "%'
  ");
  $qry_tmonth->change = null;
  $qry_tmonth->added = null;
  if($qry_lmonth->pageview <> 0) {
    $pc = round( 100 * ($qry_tmonth->pageview / $qry_lmonth->pageview ) - 100,1);
    if($pc >= 0) $pc = "+" . $pc;
    $qry_tmonth->change = "<code> (" . $pc . "%)</code>";
  }
  $result = $result. "<td>" . $qry_tmonth->pageview . $qry_tmonth->change . "</td>\n";

  #TARGET
  $qry_tmonth->target = round($qry_tmonth->pageview / (
    (date("d", current_time('timestamp')) - 1 +
    (date("H", current_time('timestamp')) +
    (date("i", current_time('timestamp')) + 1)/ 60.0) / 24.0)) * date("t", current_time('timestamp'))
  );
  if($qry_lmonth->pageview <> 0) {
    $pt = round( 100 * ($qry_tmonth->target / $qry_lmonth->pageview ) - 100,1);
    if($pt >= 0) $pt = "+" . $pt;
    $qry_tmonth->added = "<code> (" . $pt . "%)</code>";
  }
  $result = $result. "<td>" . $qry_tmonth->target . $qry_tmonth->added . "</td>\n";

  #YESTERDAY
  $qry_y = $wpdb->get_row("
   SELECT count(date) as pageview
   FROM $table_name
   WHERE
     feed='' AND
     spider='' AND
     date = '$yesterday'
   ");
  $result = $result."<td>" . $qry_y->pageview . "</td>\n";

  #TODAY
  $qry_t = $wpdb->get_row("
    SELECT count(date) as pageview
    FROM $table_name
    WHERE
      feed='' AND
      spider='' AND
      date = '$today'
    ");
  $result = $result."<td>" . $qry_t->pageview . "</td>\n";
  $result = $result."</tr>";

  ################################################################################################
  # SPIDERS ROW
  $result = $result. "<tr><td class='test spiders'>". __('Spiders','newstatpress'). "</td>"; // add by chab

  // $result = $result."<tr><td><div style='background:$spider_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>". __('Spiders','newstatpress'). "</td>";

  #TOTAL
  $qry_total = $wpdb->get_row("
    SELECT count(date) as spiders
    FROM $table_name
    WHERE
      feed='' AND
      spider<>''
  ");
  $result = $result."<td>" . $qry_total->spiders . "</td>\n";

  #LAST MONTH
  $prec=0;
  $qry_lmonth = $wpdb->get_row("
    SELECT count(date) as spiders
    FROM $table_name
    WHERE
      feed='' AND
      spider<>'' AND
      date LIKE '" . $lastmonth . "%'
  ");
  $result = $result. "<td>" . $qry_lmonth->spiders. "</td>\n";

  #THIS MONTH
  $prec=$qry_lmonth->spiders;
  $qry_tmonth = $wpdb->get_row("
    SELECT count(date) as spiders
    FROM $table_name
    WHERE
      feed='' AND
      spider<>'' AND
      date LIKE '" . $thismonth . "%'
  ");
  $qry_tmonth->change = null;
  $qry_tmonth->added = null;
  if($qry_lmonth->spiders <> 0) {
    $pc = round( 100 * ($qry_tmonth->spiders / $qry_lmonth->spiders ) - 100,1);
    if($pc >= 0) $pc = "+" . $pc;
    $qry_tmonth->change = "<code> (" . $pc . "%)</code>";
  }
  $result = $result. "<td>" . $qry_tmonth->spiders . $qry_tmonth->change . "</td>\n";

  #TARGET
  $qry_tmonth->target = round($qry_tmonth->spiders / (
    (date("d", current_time('timestamp')) - 1 +
    (date("H", current_time('timestamp')) +
    (date("i", current_time('timestamp')) + 1)/ 60.0) / 24.0)) * date("t", current_time('timestamp'))
  );
  if($qry_lmonth->spiders <> 0) {
    $pt = round( 100 * ($qry_tmonth->target / $qry_lmonth->spiders ) - 100,1);
    if($pt >= 0) $pt = "+" . $pt;
    $qry_tmonth->added = "<code> (" . $pt . "%)</code>";
  }
  $result = $result. "<td>" . $qry_tmonth->target . $qry_tmonth->added . "</td>\n";

  #YESTERDAY
  $qry_y = $wpdb->get_row("
    SELECT count(date) as spiders
    FROM $table_name
    WHERE
      feed='' AND
      spider<>'' AND
      date = '$yesterday'
  ");
  $result = $result. "<td>" . $qry_y->spiders . "</td>\n";

  #TODAY
  $qry_t = $wpdb->get_row("
    SELECT count(date) as spiders
    FROM $table_name
    WHERE
     feed='' AND
     spider<>'' AND
     date = '$today'
  ");
  $result = $result. "<td>" . $qry_t->spiders . "</td>\n";
  $result = $result. "</tr>";

  ################################################################################################
  # FEEDS ROW
  $result = $result. "<tr><td class='test feeds'>". __('Feeds','newstatpress'). "</td>"; // add by chab

  #TOTAL
  $qry_total = $wpdb->get_row("
    SELECT count(date) as feeds
    FROM $table_name
    WHERE
      feed<>'' AND spider=''
    ");
  $result = $result. "<td>".$qry_total->feeds."</td>\n";

  #LAST MONTH
  $qry_lmonth = $wpdb->get_row("
    SELECT count(date) as feeds
    FROM $table_name
    WHERE
      feed<>'' AND
      spider='' AND
      date LIKE '" . $lastmonth . "%'
  ");
  $result = $result. "<td>".$qry_lmonth->feeds."</td>\n";

  #THIS MONTH
  $qry_tmonth = $wpdb->get_row("
    SELECT count(date) as feeds
    FROM $table_name
    WHERE
      feed<>'' AND
      spider='' AND
      date LIKE '" . $thismonth . "%'
  ");
  $qry_tmonth->change = null;
  $qry_tmonth->added = null;
  if($qry_lmonth->feeds <> 0) {
    $pc = round( 100 * ($qry_tmonth->feeds / $qry_lmonth->feeds ) - 100,1);
    if($pc >= 0) $pc = "+" . $pc;
    $qry_tmonth->change = "<code> (" . $pc . "%)</code>";
  }
  $result = $result. "<td>" . $qry_tmonth->feeds . $qry_tmonth->change . "</td>\n";

  #TARGET
  $qry_tmonth->target = round($qry_tmonth->feeds / (
    (date("d", current_time('timestamp')) - 1 +
    (date("H", current_time('timestamp')) +
    (date("i", current_time('timestamp')) + 1)/ 60.0) / 24.0)) * date("t", current_time('timestamp'))
  );
  if($qry_lmonth->feeds <> 0) {
    $pt = round( 100 * ($qry_tmonth->target / $qry_lmonth->feeds ) - 100,1);
    if($pt >= 0) $pt = "+" . $pt;
    $qry_tmonth->added = "<code> (" . $pt . "%)</code>";
  }
  $result = $result. "<td>" . $qry_tmonth->target . $qry_tmonth->added . "</td>\n";

  $qry_y = $wpdb->get_row("
    SELECT count(date) as feeds
    FROM $table_name
    WHERE
      feed<>'' AND
      spider='' AND
      date = '".$yesterday."'
  ");
  $result = $result. "<td>".$qry_y->feeds."</td>\n";

  $qry_t = $wpdb->get_row("
    SELECT count(date) as feeds
    FROM $table_name
    WHERE
      feed<>'' AND
      spider='' AND
      date = '$today'
  ");
  $result = $result. "<td>".$qry_t->feeds."</td>\n";

  $result = $result. "</tr></table><br />\n\n";

  ################################################################################################
  ################################################################################################
  # THE GRAPHS

  # last "N" days graph  NEW
  $gdays=get_option('newstatpress_daysinoverviewgraph'); if($gdays == 0) { $gdays=20; }
  $start_of_week = get_option('start_of_week');
  $overview_graph.='<table width="100%" border="0"><tr>';
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
  for($gg=$gdays-1;$gg>=0;$gg--) {
    #TOTAL VISITORS
    $qry_visitors = $wpdb->get_row("
      SELECT count(DISTINCT ip) AS total
      FROM $table_name
      WHERE
        feed='' AND
        spider='' AND
        date = '".gmdate('Ymd', current_time('timestamp')-86400*$gg)."'
    ");
    $px_visitors = round($qry_visitors->total*100/$maxxday);

    #TOTAL PAGEVIEWS (we do not delete the uniques, this is falsing the info.. uniques are not different visitors!)
    $qry_pageviews = $wpdb->get_row("
      SELECT count(date) as total
      FROM $table_name
      WHERE
        feed='' AND
        spider='' AND
        date = '".gmdate('Ymd', current_time('timestamp')-86400*$gg)."'
    ");
    $px_pageviews = round($qry_pageviews->total*100/$maxxday);

    #TOTAL SPIDERS
    $qry_spiders = $wpdb->get_row("
      SELECT count(ip) AS total
      FROM $table_name
      WHERE
        feed='' AND
        spider<>'' AND
        date = '".gmdate('Ymd', current_time('timestamp')-86400*$gg)."'
      ");
    $px_spiders = round($qry_spiders->total*100/$maxxday);

    #TOTAL FEEDS
    $qry_feeds = $wpdb->get_row("
      SELECT count(ip) AS total
      FROM $table_name
      WHERE
        feed<>'' AND
        spider='' AND
        date = '".gmdate('Ymd', current_time('timestamp')-86400*$gg)."'
    ");
    $px_feeds = round($qry_feeds->total*100/$maxxday);

    $px_white = 100 - $px_feeds - $px_spiders - $px_pageviews - $px_visitors;

    $overview_graph.='<td width="'.$gd.'" valign="bottom"';
    if($start_of_week == gmdate('w',current_time('timestamp')-86400*$gg)) {
      $overview_graph.=' style="border-left:2px dotted gray; padding-left: 0px; padding-right: 0px;"';
    }  # week-cut
    $overview_graph.="><div class='testons'>
       <div style='background:#ffffff;width:100%;height:".$px_white."px;'></div>
       <div class='visitors_bar' style='height:".$px_visitors."px;' title='".$qry_visitors->total." ".__('Visitors','newstatpress')."'></div>
       <div class='web_bar' style='height:".$px_pageviews."px;' title='".$qry_pageviews->total." ".__('Pageviews','newstatpress')."'></div>
       <div class='spiders_bar' style='height:".$px_spiders."px;' title='".$qry_spiders->total." ".__('Spiders','newstatpress')."'></div>
       <div class='feeds-r' style='height:".$px_feeds."px;' title='".$qry_feeds->total." ".__('Feeds','newstatpress')."'></div>
       <div style='background:gray;width:100%;height:1px;'></div>
       <br />".gmdate('d', current_time('timestamp')-86400*$gg) . ' ' . gmdate('M', current_time('timestamp')-86400*$gg) . "</div></td>\n";
  }
  $overview_graph.='</tr></table></div>';

  # END OF OVERVIEW
  ####################################################################################################
  $result=$result.$overview_graph;
  if ($print) print $result;
  else return $result;
}

// Create the function use in the action hook

/**
 * Add the dashboard widget if option for that is on
 */
function iri_add_dashboard_widgets() {
  global $wp_meta_boxes;

  if (get_option('newstatpress_dashboard')=='checked') {
    wp_add_dashboard_widget('iri_dashboard_widget', 'NewStatPress Overview', 'iri_dashboard_widget_function');
  } else unset($wp_meta_boxes['dashboard']['side']['core']['wp_dashboard_setup']);
}

/**
 * Set the header for the page.
 * It loads google api
 */
function iri_page_header() {
  echo '<script type="text/javascript" src="http://www.google.com/jsapi"></script>';
  echo '<script type="text/javascript">';
  echo 'google.load(\'visualization\', \'1\', {packages: [\'geochart\']});';
  echo '</script>';
}

load_plugin_textdomain('newstatpress', 'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/locale', '/'.dirname(plugin_basename(__FILE__)).'/locale');

/**
 * Count this site as a newstatpress user in anonymous form (it stores inside newstatpress.altervista.org database)
 */
function new_count_register() {
  global $_NEWSTATPRESS;
  $site=$_SERVER['HTTP_HOST'];
  print "<br><iframe width=0 height=0 src=http://newstatpress.altervista.org/register.php?site=".$site."&ver=".$_NEWSTATPRESS['version']."></iframe>";
}

/**
 * Remove this site as a newstatpress user
 */
function new_count_deregister() {
  global $_NEWSTATPRESS;
  $site=$_SERVER['HTTP_HOST'];
  print "<br><iframe width=0 height=0 src=http://newstatpress.altervista.org/deregister.php?site=".$site."></iframe>";
}

/**
 * Get the total number of sites that use newstatpress
 *
 * @return the total number of site that use newstatpress
 */
function new_count_total() {
  if (version_compare(phpversion(), '5.0.0', '>=')) {
    // prevent that if my site is slow this plugin slow down your
    $ctx=stream_context_create(array('http'=> array( 'timeout' => 1)));
    $result=@file_get_contents('http://newstatpress.altervista.org/total.php', false, $ctx);
  } else $result=@file_get_contents('http://newstatpress.altervista.org/total.php');

  return $result;
}

/**
 * check for update of the plugin
 */
function newstatpress_update() {
  global $_NEWSTATPRESS;

  $active_version = get_option('newstatpress_version', '0' );

  if (version_compare( $active_version, $_NEWSTATPRESS['version'], '<' )) {
    update_option('newstatpress_version', $_NEWSTATPRESS['version']);

    new_count_register();
  }
}


add_action('plugins_loaded', 'widget_newstatpress_init');
add_action('send_headers', 'iriStatAppend');  //add_action('wp_head', 'iriStatAppend');
add_action('init','iri_checkExport');
add_action( 'admin_init', 'newstatpress_update' );
###add_action('wp_head', 'iri_page_header');

// Hoook into the 'wp_dashboard_setup' action to register our other functions
add_action('wp_dashboard_setup', 'iri_add_dashboard_widgets' );

add_filter('the_content', 'content_newstatpress');

register_activation_hook(__FILE__,'iri_NewStatPress_CreateTable');
register_deactivation_hook( __FILE__, 'new_count_deregister' );

?>
