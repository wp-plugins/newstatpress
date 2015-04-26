<?php

/****** List of Functions available ******
 *
 * nsp_DisplayToolsPage()
 * nsp_RemovePluginDatabase()
 * nsp_IP2nationDownload()
 * nsp_ExportNow()
 * nsp_Export()
 *****************************************/

function nsp_DisplayToolsPage() {

  global $pagenow;
  $page='nsp_tools';
  $ToolsPage_tabs = array( 'IP2nation' => __('IP2nation','newstatpress'),
                            'update' => __('Update','newstatpress'),
                            'export' => __('Export','newstatpress'),
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
      // Importation if requested by user
      if (isset($_POST['download']) && $_POST['download'] == 'yes' ) {
        $install_result=nsp_IP2nationDownload();
      }
      ?>
      <div class='wrap'><h3><?php _e('To import IP2nation database','newstatpress'); ?></h3>

        <?php
        if ( isset($install_result) AND $install_result !='') {
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

      </div><?php
      break;

      case 'export' :
      nsp_Export();
      break;

      case 'update' :
      // database update if requested by user
      if (isset($_POST['update']) && $_POST['update'] == 'yes' ) {
        iriNewStatPressUpdate();
        die;
      }
      ?>
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
    </div><?php
      break;

      case 'remove' :
      nsp_RemovePluginDatabase();
      break;
    }
  }
}


// add by chab
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
      $install_status=__('Instalation of IP2nation database was successful','newstatpress');
    }

    // Remove Zip file
    unlink( $temp_zip_file );
    return $install_status;
}



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
          <div class='error'><p>
        <?php _e('Warning: pressing the below button will make all your stored data to be erased!',"newstatpress"); ?>
      </p></div>
        <form method=post>
        <?php
        _e("It is added for the people that did not want to use the plugin anymore and so they want to remove the stored data.","newstatpress");
        echo "<br />";
        _e("If you are in doubt about this function, don't use it.","newstatpress");
        ?>
        <br /><br />
        <input class='button button-primary' type=submit value="<?php _e('Remove','newstatpress'); ?>" onclick="return confirm('<?php _e('Are you sure?','newstatpress'); ?>');" >
        <input type=hidden name=removeit value=yes>
        </form>
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
 */
function iriNewStatPress_extractfeedreq($url) {
  list($null,$q)=explode("?",$url);
  if (strpos($q, "&")!== false) list($res,$null)=explode("&",$q);
  else $res=$q;
  return $res;
}

 /**
  * Performes database update with new definitions
  */
 function iriNewStatPressUpdate() {
   global $wpdb;
   global $newstatpress_dir;

   $table_name = nsp_TABLENAME;

   $wpdb->flush();     // flush for counting right the queries
   $start_time = microtime(true);

   $days=nsp_DurationToDays();  // get the number of days for the update

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
   nsp_BuildPluginSQLTable('update');

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
   print "<td>".iritablesize($wpdb->prefix."statpress")."</td>"; // todo chab : to clean
   print "<td><img class'update_img' src='$img_ok'></td></tr>";

   print "<tr><td>". __('Final Index','newstatpress'). " $table_name</td>";
   print "<td>".iriindextablesize($wpdb->prefix."statpress")."</td>"; // todo chab : to clean
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

 ?>
