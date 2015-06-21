<?php

/**
 * Show statistics in dashboard
 *
 *******************************/
function nsp_BuildDashboardWidget() {

  nsp_MakeOverview('dashboard');
  ?>
  <ul class='nsp_dashboard'>
    <li>
      <a href='admin.php?page=nsp_details'><?php _e('Details','newstatpress')?></a> |
    </li>
    <li>
      <a href='admin.php?page=nsp_visits'><?php _e('Visits','newstatpress')?></a> |
    </li>
    <li>
      <a href='admin.php?page=nsp_options'><?php _e('Options','newstatpress')?>
      </li>
  </ul>
  <?php
}

// Create the function use in the action hook
function nsp_AddDashBoardWidget() {

  global $wp_meta_boxes;
  $title=__('NewStatPress Overview','newstatpress');

  //Add the dashboard widget if user option is 'yes'
  if (get_option('newstatpress_dashboard')=='checked')
    wp_add_dashboard_widget('dashboard_NewsStatPress_overview', $title, 'nsp_BuildDashboardWidget');
  else unset($wp_meta_boxes['dashboard']['side']['core']['wp_dashboard_setup']);

}
?>
