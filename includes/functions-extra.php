<?php

function iriNewStatPressCredits() {

  $contributors = array(
    array('Stefano Tognon', 'NewStatPress develoup'),
    array('cHab', 'NewStatPress collaborator'),
    array('Daniele Lippi', 'Original StatPress develoup'),
    array('Sisko', 'Open link in new tab/window<br>New displays of data for spy function<br>'),
    array('from wp_slimstat', 'Add option for not track given IPs<br /> Add option for not track given permalinks'),
    array('Ladislav', 'Let Search function to works again'),
    array('from statpress-visitors', 'Add new OS (+44), browsers (+52) and spiders (+71)<br /> Add in the option the ability to update in a range of date<br /> New spy and bot'),
    array('Maurice Cramer','Add dashboard widget<br /> Fix total since in overwiew<br /> Fix missing browser image and IE aligment failure in spy section<br /> Fix nation image display in spy'),
    array('Ruud van der Veen', 'Add tab delimiter for exporting data'),
    array('kjmtsh', 'Many fixes about empty query result and obsolete functions'),
    array('shilom', 'French translation Update'),
    array('Alphonse PHILIPPE', 'French translation Update'),
    array('Vincent G.', 'Lithuanian translation Addition'),
    array('Christopher Meng', 'Simplified Chinese translation Addition'),
    array('godOFslaves', 'Russian translation Update'),
    array('Branco', 'Slovak translation Addition'),
    array('Peter Bago', 'Hungarian translation Addition'),
    array('Boulis Antoniou', 'Greek translation Addition'),
    array('Michael Yunat', 'Ukranian translation Addition'),
    array('Pawel Dworniak', 'Polish translation Update')
  );
  echo "<div class='wrap'><h2>"; _e('Credits','newstatpress'); echo "</h2>";
  echo "<br /><table id='credit'>\n";
  echo "<thead>\n<tr><th class='cell-l'>";  _e('Contributor','newstatpress'); echo "</th>\n<th class='cell-r'>"; _e('Description','newstatpress'); echo "</th></tr>\n</thead>\n<tbody>";

  foreach($contributors as $contributors)
  {
    list($name, $contribution) = $user;
    echo "<tr>\n";
    echo "<td class='cell-l'>$contributors[0]</td>\n";
    echo "<td class='cell-r'>$contributors[1]</td>\n";
    echo "</tr>\n";
  };
  echo "<tbody></table></div>";

  echo "<br /><div><table>\n";
  echo "<tr>\n<td>"; _e('Plugin homepage','newstatpress'); echo ": <a target='_blank' href='http://newstatpress.altervista.org'>Newstatpress</a></td></tr>";
  echo "<tr>\n<td>"; _e('RSS news','newstatpress'); echo ": <a target='_blank' href='http://newstatpress.altervista.org/?feed=rss2'>"; _e('News','newstatpress'); echo "</a></td></tr>";
  echo "</tr></table></div><br />";
  echo "  <form  method='post' action='https://www.paypal.com/cgi-bin/webscr'>
      <input type='hidden' value='_s-xclick' name='cmd'></input>
      <input type='hidden' value='F5S5PF4QBWU7E' name='hosted_button_id'></input>
      <input class='button button-primary' type=submit value='"; _e('Make a donation','newstatpress');
echo "'></form>";


}


/**
 * Generate HTML for remove menu in Wordpress
 */
function iriNewStatPressRemove() {

  if(isset($_POST['removeit']) && $_POST['removeit'] == 'yes') {
    global $wpdb;
    $table_name = $wpdb->prefix . "statpress";
    $results =$wpdb->query( "DELETE FROM " . $table_name);
    print "<br /><div class='remove'><p>".__('All data removed','newstatpress')."!</p></div>";
  }
  else {
      ?>
        <div class='wrap'><h2><?php _e('Remove NewStatPress database','newstatpress'); ?></h2>
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



?>
