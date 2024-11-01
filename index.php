<?php
/*
Plugin Name: Statistics 123
Plugin URI: https://wordpress.org/plugins/statistics-123/
Description: Plugin for that, you can see count of visitor's views on your website.
Version: 1.3
Author: Chugaev Aleksandr Aleksandrovich
Author URI: https://profiles.wordpress.org/aleksandrposs/
*/

function ip_visitor_country($visitor_ip)
{

    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];
    $country  = "Unknown";

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://www.geoplugin.net/json.gp?ip=".$visitor_ip);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $ip_data_in = curl_exec($ch); // string
    curl_close($ch);

    $ip_data = json_decode($ip_data_in,true);
    $ip_data = str_replace('&quot;', '"', $ip_data); // for PHP 5.2 see stackoverflow.com/questions/3110487/

    if($ip_data && $ip_data['geoplugin_countryName'] != null) {
        $country = $ip_data['geoplugin_countryName'];
    }

    return $country;
}

function statistics_123_namespace_123() {
    wp_register_style('my_admin_123_style', plugins_url('style.css',__FILE__ ));
    wp_enqueue_style('my_admin_123_style');
}
add_action( 'admin_init','statistics_123_namespace_123');


function statistics_123_getRealUserIp(){
    switch(true){
      case (!empty(sanitize_text_field($_SERVER['HTTP_X_REAL_IP']))) : return sanitize_text_field($_SERVER['HTTP_X_REAL_IP']);
      case (!empty(sanitize_text_field($_SERVER['HTTP_CLIENT_IP']))) : return sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
      case (!empty(sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']))) : return sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
      default : return sanitize_text_field($_SERVER['REMOTE_ADDR']);
    }
 }
 
 function statistics_123_get_all_table(){
      global $wpdb;
        $stat_at_present_day = $wpdb->get_results( "SELECT * FROM `" . $wpdb->prefix . "plugin_statistics_123_visitor_log` " );
        $mas_el = $el;
        foreach ($stat_at_present_day as $s) {
                $t = array($s);
                $mas_el[] = (array)$t[0];
        }
        return $mas_el;
}
 
 function statistics_123_install() {  // install plugin
  global $wpdb;
   
  // create table "orders"
 $table = $wpdb->prefix . "plugin_statistics_123_visitor_log";
  if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {	
	$sql = "CREATE TABLE `" . $table . "` (
	  `visitor_id` int(9) NOT NULL AUTO_INCREMENT,
          `visitor_date` DATE NOT NULL,
	  `visitor_ip_address` VARCHAR(15) NOT NULL,
   	  `visitor_country` VARCHAR(15) NOT NULL,
	  UNIQUE KEY `id` (visitor_id)
	) DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;";
	  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	  dbDelta($sql);
 }
 
 
}
register_activation_hook( __FILE__,'statistics_123_install');

function statistics_123_uninstall() { // uninstall plugin
 
 global $wpdb;
 $table = $wpdb->prefix . "plugin_statistics_123_visitor_log";	
 $wpdb->query("DROP TABLE IF EXISTS $table");
}
register_deactivation_hook( __FILE__,'statistics_123_uninstall');

	
// Hook for adding admin menus
add_action('admin_menu', 'statistics_123_add_pages');
function statistics_123_add_pages() {
    // Add a new submenu under Tools:
    add_management_page( __('(Visitor) Statistics 123','menu-test'), __('(Visitor) Statistics 123','menu-test'), 'manage_options', 'statistics_123_admin', 'statistics_123_tools_page');   
}

// displays the page content for the Test Tools submenu
function statistics_123_tools_page() {
    
        global $wpdb;
        $table = $wpdb->prefix . "plugin_statistics_123_visitor_log";
        $res_count_all = $wpdb->get_results( "SELECT count(*) FROM $table;");
        $count_all= (array) $res_count_all[0];
        echo "All views : " . esc_html($count_all["count(*)"]) . "<br><br>";
      
        
       if (isset($_GET['page_detail_stat']) ) {
            echo '<a href="/wp-admin/tools.php?page=statistics_123_admin">Main Admin Page of Plugin</a><br>';
      
          $table = $wpdb->prefix . "plugin_statistics_123_visitor_log";
          $res_count_all = $wpdb->get_results("SELECT * FROM $table WHERE visitor_date='" .$_GET['by_day'] . "';");
          $count_array=count($res_count_all);
          foreach($res_count_all as $ra) {
               $test_array_string = (array) $ra;
               echo "date: " . $test_array_string["visitor_date"] . ' ip: ' . $test_array_string["visitor_ip_address"] . ' country:' . $test_array_string["visitor_country"] . '<br>';
          }
          
       } else {
                     
        $res = $wpdb->get_results( "SELECT visitor_date, count(*) FROM $table GROUP BY visitor_date ORDER BY visitor_date DESC LIMIT 10;");
        $ter = array();
        echo '<div id="tools_plugin_table_left">Count of Views</div>';
        echo '<div id="tools_plugin_table_right">Date</div>';
        echo '<div id="tools_plugin_table_right">More</div>';
        echo '<div id="tools_plugin_table_end"></div>';
        foreach ($res as $r) {
                  $ter = (array) $r;
                    echo '<div id="tools_plugin_table_left">' . esc_html($ter['count(*)']) . '</div>';
                    echo '<div id="tools_plugin_table_right">' . esc_html($ter['visitor_date']) . '</div>';
                    echo '<div id="tools_plugin_table_right"><a href="/wp-admin/tools.php?page=statistics_123_admin&page_detail_stat=1&by_day=' . $ter['visitor_date'] .'">Detail stat by day</a></div>';                   
                    echo '<div id="tools_plugin_table_end"></div>';
        }
       
       }
}

/////// INIT /////

function statistics_123(){
   global $wpdb;
  
   if (!is_admin()) {
    $wpdb->insert($wpdb->prefix . "plugin_statistics_123_visitor_log", array(
		  "visitor_date" =>date("Y-m-d"),
                  "visitor_ip_address" => statistics_123_getRealUserIp() ,
                  "visitor_country" => ip_visitor_country(statistics_123_getRealUserIp())
	               )
                );
            }
}
add_action('init', 'statistics_123');
?>