<?php

/****** List of Functions available ******
 *
 * nsp_DisplayToolsPage()
 * nsp_RemovePluginDatabase()
 * nsp_IP2nationDownload()
 * nsp_ExportNow()
 * nsp_Export()
 *****************************************/

/**
 * Display the tools page using tabs
 */
function nsp_DisplayToolsPage() {

  global $pagenow;
  $page='nsp_tools';
  $ToolsPage_tabs = array( 'IP2nation' => __('IP2nation','newstatpress'),
                            'update' => __('Update','newstatpress'),
                            'export' => __('Export','newstatpress'),
                            'optimize' => __('Optimize','newstatpress'),
                            'repair' => __('Repair','newstatpress'),
                            'remove' => __('Remove','newstatpress')
                          );

  $default_tab='IP2nation';

  print "<div class='wrap'><h2>".__('Database Tools','newstatpress')."</h2>";

  if ( isset ( $_GET['tab'] ) ) nsp_DisplayTabsNavbarForMenuPage($ToolsPage_tabs,$_GET['tab'],$page);
  else nsp_DisplayTabsNavbarForMenuPage($ToolsPage_tabs, $default_tab, $page);

  if ( $pagenow == 'admin.php' && $_GET['page'] == $page ) {

    if ( isset ( $_GET['tab'] ) ) $tab = $_GET['tab'];
    else $tab = $default_tab;

    switch ($tab) {

      case 'IP2nation' :
      nsp_IP2nation();
      break;

      case 'export' :
      nsp_Export();
      break;

      case 'update' :
      nsp_Update();
      break;

      case 'optimize' :
      nsp_Optimize();
      break;

      case 'repair' :
      nsp_Repair();
      break;

      case 'remove' :
      nsp_RemovePluginDatabase();
      break;
    }
  }
}

/**
 * IP2nation form function
 *
 *************************/
function nsp_IP2nation() {



  // Install or Remove if requested by user
  if (isset($_POST['installation']) && $_POST['installation'] == 'install' ) {
    $install_result=nsp_IP2nationInstall();
  }
  elseif (isset($_POST['installation']) && $_POST['installation'] == 'remove' ) {
    $install_result=nsp_IP2nationRemove();
  }

  // Display message if present
  if (isset($install_result) AND $install_result !='') {
    print "<br /><div class='updated'><p>".__($install_result,'newstatpress')."</p></div>";
  }

  global $nsp_option_vars;
  global $wpdb;

  //Create IP2nation variable if not exists: value 'none' by default or date when installed
  $installed=get_option($nsp_option_vars['ip2nation']['name']);
  if ($installed=="") {
    add_option( $nsp_option_vars['ip2nation']['name'], $nsp_option_vars['ip2nation']['value'],'','yes');
  }

  echo "<br /><br />";
     $file_ip2nation= WP_PLUGIN_DIR . '/' .dirname(plugin_basename(__FILE__)) . '/includes/ip2nation.sql';
     $date=date('d/m/Y', filemtime($file_ip2nation));

     $table_name = "ip2nation";
     if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
       $value_remove="none";
       $class_inst="desactivated";
       $installed=$nsp_option_vars['ip2nation']['value'];
     }
     else {
         $value_remove="remove";
         $class_inst="";
         $installed=get_option($nsp_option_vars['ip2nation']['name']);
         if($installed=='none')
          $installed=__('unknow','newstatpress');
     }

    // Display status
    $i=sprintf(__('Last version available: %s','newstatpress'), $date);
    echo $i.'<br />';
     if ($installed!="none") {
       $i=sprintf(__('Last version installed: %s','newstatpress'), $installed);
       echo $i.'<br /><br />';
       _e('To update the IP2nation database, just click on the button bellow.','newstatpress');
       if($installed==$date) {
         $button_name='Update';
         $value_install='none';
         $class_install="desactivated";
       }
       else {
         $button_name='Install';
       }
     }
     else {
       _e('Last version installed: none ','newstatpress');
       echo '<br /><br />';
       _e('To download and to install the IP2nation database, just click on the button bellow.','newstatpress');
       $button_name='Install';
     }


    ?>

    <br /><br />
      <form method=post>
       <input type=hidden name=page value=newstatpress>

       <input type=hidden name=newstatpress_action value=ip2nation>
       <button class='<?php echo $class_install ?> button button-primary' type=submit name=installation value=install>
         <?php _e($button_name,'newstatpress'); ?>
       </button>

       <input type=hidden name=newstatpress_action value=ip2nation>
       <button class='<?php echo $class_inst ?> button button-primary' type=submit name=installation value=<?php echo $value_remove ?> >
         <?php _e('Remove','newstatpress'); ?>
       </button>
      </form>
    </div>

    <div class='update-nag help'>

    <?php
    _e('What is ip2nation?','newstatpress');
    echo "<br/>";
    _e('ip2nation is a free MySQL database that offers a quick way to map an IP to a country. The database is optimized to ensure fast lookups and is based on information from ARIN, APNIC, RIPE etc. You may install the database using the link to the left. (see: <a href="http://www.ip2nation.com/">http://www.ip2nation.com</a>)','newstatpress');
    echo "<br/><br />
          <span class='strong'>"
            .__('Note: The installation may take some times to complete.','newstatpress').
         "</span>";

    ?>
    </div>
<?php
}

// add by chab
/**
 * Download and install IP2nation
 *
 * @return the status of the operation
 *************************************/
function nsp_IP2nationDownload() {

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
    $install_status=__('Installation of IP2nation database was successful','newstatpress');
  }

  // Remove Zip file
  unlink( $temp_zip_file );
  return $install_status;
}

//TODO integrate error check
function nsp_IP2nationInstall() {

  global $wpdb;
  global $nsp_option_vars;

  $file_ip2nation= WP_PLUGIN_DIR . '/' .dirname(plugin_basename(__FILE__)) . '/includes/ip2nation.sql';

  $sql = file_get_contents($file_ip2nation);
  $sql_array = explode (";",$sql);
  foreach ($sql_array as $val) {
    $wpdb->query($val);

  }
  $date=date('d/m/Y', filemtime($file_ip2nation));
  // echo $date;
  update_option($nsp_option_vars['ip2nation']['name'], $date);
  $install_status=__('Installation of IP2nation database was successful','newstatpress');

 return $install_status;
}

//TODO integrate error check
function nsp_IP2nationRemove() {

  global $wpdb;

  $sql = "DROP TABLE IF EXISTS ip2nation;";
  $wpdb->query($sql);
  $sql ="DROP TABLE IF EXISTS ip2nationCountries;";
  $wpdb->query($sql);

  update_option($nsp_option_vars['ip2nation']['name'], $nsp_option_vars['ip2nation']['value']);

  $install_status=__('IP2nation database was remove successfully','newstatpress');

 return $install_status;
}


/**
 * Export form function
 */
function nsp_Export() {
?>
<!--TODO chab, check if the input format is ok  -->
  <div class='wrap'><h3><?php _e('Export stats to text file','newstatpress'); ?> (csv)</h3>
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
 * Export the NewStatPress data
 */
function nsp_ExportNow() {
  global $wpdb;
  $table_name = nsp_TABLENAME;
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
 * Generate HTML for remove menu in Wordpress
 */
function nsp_RemovePluginDatabase() {

  if(isset($_POST['removeit']) && $_POST['removeit'] == 'yes') {
    global $wpdb;
    $table_name = nsp_TABLENAME;
    $results =$wpdb->query( "DELETE FROM " . $table_name);
    print "<br /><div class='remove'><p>".__('All data removed','newstatpress')."!</p></div>";
  }
  else {
      ?>

        <div class='wrap'><h3><?php _e('Remove NewStatPress database','newstatpress'); ?></h3>
          <br />

        <form method=post>
              <?php _e('To remove the Newstatpress database, just click on the button bellow.','newstatpress');?>
          <br /><br />
        <input class='button button-primary' type=submit value="<?php _e('Remove','newstatpress'); ?>" onclick="return confirm('<?php _e('Are you sure?','newstatpress'); ?>');" >
        <input type=hidden name=removeit value=yes>
        </form>
        <div class='update-nag help'>
          <?php
            _e("This operation will remove all collected data by NewStatpress. This function is useful at people who did not want use the plugin any more or who want simply purge the stored data.","newstatpress");
          ?>
          <br />
          <span class='strong'>
          <?php _e("If you have doubt about this function, don't use it.","newstatpress"); ?>
        </span>
       </div>
       <div class='update-nag warning'><p>
     <?php _e('Warning: pressing the below button will make all your stored data to be erased!',"newstatpress"); ?>
   </p></div>
        </div>
      <?php
  }
}

/**
 * Get the days a user has choice for updating the database
 *
 * @return the number of days of -1 for all days
 */
function nsp_DurationToDays() {

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
 * Extract the feed from the given url
 *
 * @param url the url to parse
 * @return the extracted url
 *************************************/
function nsp_ExtractFeedReq($url) {
  list($null,$q)=explode("?",$url);
  if (strpos($q, "&")!== false) list($res,$null)=explode("&",$q);
  else $res=$q;
  return $res;
}

/**
 * Update form function
 *
 ***********************/
function nsp_Update() {
  // database update if requested by user
  if (isset($_POST['update']) && $_POST['update'] == 'yes' ) {
    nsp_UpdateNow();
    die;
  }
  ?>
  <div class='wrap'>
   <h3><?php _e('Database update','newstatpress'); ?></h3>
       <?php _e('To update the newstatpress database, just click on the button bellow.','newstatpress');?>
   <br /><br />
   <form method=post>
    <input type=hidden name=page value=newstatpress>
    <input type=hidden name=update value=yes>
    <input type=hidden name=newstatpress_action value=update>
    <button class='button button-primary' type=submit><?php _e('Update','newstatpress'); ?></button>
   </form>
  </div>

  <div class='update-nag help'>

  <?php

  _e('Update the database is particularly useful when the ip2nation data and definitions data (OS, browser, spider) have been updated. An option in future will allow an automatic update of the database..','newstatpress');
  echo "<br/><br />
        <span class='strong'>"
          .__('Note: The update may take some times to complete.','newstatpress').
       "</span>";

  ?>
  </div>

  <?php
}

/**
 * Performes database update with new definitions
 */
function nsp_UpdateNow() {
  global $wpdb;
  global $newstatpress_dir;

  $table_name = nsp_TABLENAME;

  $wpdb->flush();     // flush for counting right the queries
  $start_time = microtime(true);

  $days=nsp_DurationToDays();  // get the number of days for the update

  $to_date  = gmdate("Ymd",current_time('timestamp'));

  if ($days==-1)
    $from_date= "19990101";   // use a date where this plugin was not present
  else
    $from_date = gmdate('Ymd', current_time('timestamp')-86400*$days);

  $_newstatpress_url=PluginUrl();

  $wpdb->show_errors();

  //add by chab
  //$var requesting the absolute path
  $img_ok = $_newstatpress_url.'images/ok.gif';
  // $ip2nation_db = $newstatpress_dir.'/includes/ip2nation.sql';

  print "<div class='wrap'><h2>".__('Database Update','newstatpress')."</h2><br />";

  print "<table class='widefat nsp'><thead><tr><th scope='col'>".__('Updating...','newstatpress')."</th><th scope='col' style='width:400px;'>".__('Size','newstatpress')."</th><th scope='col' style='width:100px;'>".__('Result','newstatpress')."</th><th></th></tr></thead>";
  print "<tbody id='the-list'>";

  # update table
  nsp_BuildPluginSQLTable('update');

  echo "<tr>
          <td>". __('Structure','newstatpress'). " $table_name</td>
          <td>".nsp_TableSize($wpdb->prefix."statpress")."</td>
          <td><img class'update_img' src='$img_ok'></td>
        </tr>";

  print "<tr><td>". __('Index','newstatpress'). " $table_name</td>";
  print "<td>".nsp_IndexTableSize($wpdb->prefix."statpress")."</td>";
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
  $s=nsp_ExtractFeedReq(get_bloginfo('comments_atom_url'));
  if($s != '') {
    $wpdb->query("
      UPDATE $table_name
      SET feed='COMMENT'
      WHERE
        INSTR(urlrequested,'$s')>0 AND
        date BETWEEN $from_date AND $to_date;"
   );
  }
  $s=nsp_ExtractFeedReq(get_bloginfo('comments_rss2_url'));
  if($s != '') {
    $wpdb->query("
      UPDATE $table_name
      SET feed='COMMENT'
      WHERE
        INSTR(urlrequested,'$s')>0 AND
        date BETWEEN $from_date AND $to_date;"
    );
  }
  $s=nsp_ExtractFeedReq(get_bloginfo('atom_url'));
  if($s != '') {
    $wpdb->query("
      UPDATE $table_name
      SET feed='ATOM'
      WHERE
        INSTR(urlrequested,'$s')>0 AND
        date BETWEEN $from_date AND $to_date;"
    );
  }
  $s=nsp_ExtractFeedReq(get_bloginfo('rdf_url'));
  if($s != '') {
    $wpdb->query("
      UPDATE $table_name
      SET feed='RDF'
      WHERE
        INSTR(urlrequested,'$s')>0 AND
        date BETWEEN $from_date AND $to_date;"
    );
  }
  $s=nsp_ExtractFeedReq(get_bloginfo('rss_url'));
  if($s != '') {
    $wpdb->query("
      UPDATE $table_name
      SET feed='RSS'
      WHERE
        INSTR(urlrequested,'$s')>0 AND
        date BETWEEN $from_date AND $to_date;"
    );
  }
  $s=nsp_ExtractFeedReq(get_bloginfo('rss2_url'));
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
    list($searchengine,$search_phrase)=explode("|",nsp_GetSE($rk->referrer));
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
  print "<td>".nsp_TableSize($wpdb->prefix."statpress")."</td>"; // todo chab : to clean
  print "<td><img class'update_img' src='$img_ok'></td></tr>";

  print "<tr><td>". __('Final Index','newstatpress'). " $table_name</td>";
  print "<td>".nsp_IndexTableSize($wpdb->prefix."statpress")."</td>"; // todo chab : to clean
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

/**
 * Optimize form function
 */
function nsp_Optimize() {
  // database update if requested by user
  if (isset($_POST['optimize']) && $_POST['optimize'] == 'yes' ) {
    nsp_OptimizeNow();
    die;
  }
  ?>
  <div class='wrap'>
    <h3><?php _e('Optimize table','newstatpress'); ?></h3>
    <?php _e('To optimize the statpress table, just click on the button bellow.','newstatpress');?>
    <br /><br />
    <form method=post>
      <input type=hidden name=page value=newstatpress>
      <input type=hidden name=optimize value=yes>
      <input type=hidden name=newstatpress_action value=optimize>
      <button class='button button-primary' type=submit><?php _e('Optimize','newstatpress'); ?></button>
    </form>

    <div class='update-nag help'>
      <?php _e('Optimize a table is an database operation that can free some server space if you had lot of delation (like with prune activated) in it.','newstatpress');?>
      <br /><br />
      <span class='strong'>
        <?php _e('Be aware that this operation may take a lot of server time to finish the processing (depending on your database size). So so use it only if you know what you are doing.','newstatpress');?>
      </span>
    </div>
  </div>
  <?php
}

/**
 * Repair form function
 */
function nsp_Repair() {
  // database update if requested by user
  if (isset($_POST['repair']) && $_POST['repair'] == 'yes' ) {
    nsp_RepairNow();
    die;
  }
  ?>
  <div class='wrap'>
   <h3><?php _e('Repair table','newstatpress'); ?></h3>
       <?php _e('To repair the statpress table if damaged, just click on the button bellow.','newstatpress');?>
   <br /><br />
   <form method=post>
    <input type=hidden name=page value=newstatpress>
    <input type=hidden name=repair value=yes>
    <input type=hidden name=newstatpress_action value=repair>
    <button class='button button-primary' type=submit><?php _e('Repair','newstatpress'); ?></button>
   </form>

   <div class='update-nag help'>
     <?php _e('Repair is an database operation that can fix a corrupted table.','newstatpress');?>
    <br /><br />
    <span class='strong'>
    <?php _e('Be aware that this operation may take a lot of server time to finish the processing (depending on your database size). So so use it only if you know what you are doing.','newstatpress');?>
    </span>
   </div>
  </div><?php
}

function nsp_OptimizeNow() {
  global $wpdb;
  $table_name = nsp_TABLENAME;

  $wpdb->query("OPTIMIZE TABLE $table_name");
  print "<br /><div class='optimize'><p>".__('Optimization finished','newstatpress')."!</p></div>";
}

function nsp_RepairNow() {
  global $wpdb;
  $table_name = nsp_TABLENAME;

  $wpdb->query("REPAIR TABLE $table_name");
  print "<br /><div class='repair'><p>".__('Repair finished','newstatpress')."!</p></div>";
}

?>
