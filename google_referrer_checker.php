<?php
/*
Plugin Name: Google Referrer Checker
Plugin URI: http://gunnertech.com/2012/02/google-referrer-checker-a-wordpress-plugin/
Description: A plugin that coaxes google to index sites that link to yours
Version: 0.3.1
Author: gunnertech, codyswann
Author URI: http://gunnnertech.com
License: GPL2
*/

global $gfc_db_version;
$gfc_db_version = "0.3.1";

class GoogleReferrerChecker {
  
  static function activate() {
    global $wpdb;
    global $gfc_db_version;
         
    $table_name = $wpdb->prefix . "gfc_ref_slots";
    $banned_host_table_name = $wpdb->prefix . "gfc_banned_hosts";
  
    $sql = "CREATE TABLE " . $table_name . " (
        id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id), 
        uri VARCHAR(244) DEFAULT '' NOT NULL,
        ref VARCHAR(244) DEFAULT '' NOT NULL, INDEX (ref),
        status INT DEFAULT 1 NOT NULL, INDEX (status)
      );
      
      
      CREATE TABLE " . $banned_host_table_name . " (
          id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id), 
          host VARCHAR(244) DEFAULT '' NOT NULL, INDEX (host)
        );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  
    update_option("gfc_db_version", $gfc_db_version);
  }
  
  static function update_db_check() {
    global $gfc_db_version;
    
    $installed_ver = get_option( "gfc_db_version" );
    
    if( $installed_ver != $gfc_db_version ) {
      self::activate();
    }
  }
  
  static function deactivate() {

  }
  
  static function uninstall() {

  }
  
  static function random_update() {
    global $wpdb;
    
    $rand = rand(0,100);
    
    if($rand == 1) {
      $item = $wpdb->get_row("SELECT * FROM $wpdb->ref_slots WHERE status=1 ORDER BY rand() LIMIT 1");
      
      if(isset($item) && isset($item->uri)) {
        self::handle_ref($item->ref,$item->uri);
      }
    }
  }
  
  static function handle_ref($ref,$uri) {
    global $wpdb;
    
    if( self::is_good_ref($ref) ) {
      $items = $wpdb->get_results( "SELECT * FROM $wpdb->ref_slots WHERE ref='$ref'");
      
      if(count($items) <= 0) {
        $data = file_get_contents($ref);
        
        if(stristr($data,$uri) === FALSE) {
          $wpdb->query(
            $wpdb->prepare("INSERT INTO $wpdb->ref_slots (uri, ref, status) VALUES(%s, %s, %d)",$uri,$ref,2)
          );
        } else {
          $c = "http://webcache.googleusercontent.com/search?q=cache:$ref+&cd=1&hl=en&ct=clnk&gl=us";
          $ch = curl_init();
          
          curl_setopt($ch,CURLOPT_URL,$c);
          curl_setopt($ch,CURLOPT_REFERER,"http://www.google.com");
          curl_setopt($ch,CURLOPT_USERAGENT,"Opera/9.80 Windows NT 6.1 U en Presto/2.9.168 Version/11.50");
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
          
          $data = curl_exec($ch);
          
          if(!stristr($data,$uri)) {
            $wpdb->query(
              $wpdb->prepare("INSERT INTO $wpdb->ref_slots (uri, ref, status) VALUES(%s, %s, %d)",$uri,$ref,1)
            );
          } else {
            $wpdb->query(
              $wpdb->prepare("INSERT INTO $wpdb->ref_slots (uri, ref, status) VALUES(%s, %s, %d)",$uri,$ref,2)
            );
          }
        } 
      }
    }
  }
  
  static function is_good_ref($ref) {
    global $wpdb;
    
    $badrefs = array($_SERVER['SERVER_NAME'],"www.linkedin.com","ow.ly","www.google.co.in","t.co","www.google.co.uk","google.co.uk","google.com","bing.com","yahoo.com","blekko.com","duckduckgo.com","facebook.com","t.co","digg.com","wikipedia.org");
    
    $items = $wpdb->get_results( "SELECT * FROM $wpdb->banned_hosts"); 
    
    foreach($items as $item) {
      $badrefs[] = $item->host;
    }
    
    foreach($badrefs as $bad) { if(stristr($ref,$bad)) { return false; } }
    
    return true;
  }
  
  static function add_new() {
    if(strlen($_SERVER['HTTP_REFERER'])>1) {
      self::handle_ref($_SERVER['HTTP_REFERER'],$_SERVER['REQUEST_URI']);
    }
  }
  
  static function setup() {
    global $wpdb;
    
    $wpdb->ref_slots = $wpdb->prefix . "gfc_ref_slots";
    $wpdb->banned_hosts = $wpdb->prefix . "gfc_banned_hosts";
    
    add_action( 'admin_menu', array( 'GoogleReferrerChecker', 'admin_menu' ) );
  }
  
  static function admin_menu() {
    add_menu_page( 'Referrer Checker', 'Referrer Checker', 'manage_options', 'referrer-checker', array( 'GoogleReferrerChecker', 'admin_page' ), '' );
  }
  
  static function admin_page() { 
    global $wpdb;
    
    $status = (isset($_GET['status']) ? $_GET['status'] : '1');
    $query = ($status == 'all' ? "SELECT * FROM $wpdb->ref_slots ORDER BY status ASC" : "SELECT * FROM $wpdb->ref_slots WHERE status = $status ORDER BY status ASC");
    
    if(isset($_POST['ref_slot']) && isset($_GET['id'])) {
      $params = $_POST['ref_slot'];
      $wpdb->query(
        $wpdb->prepare("UPDATE $wpdb->ref_slots SET status = %d WHERE id = %d",$params['status'],$_GET['id'])
      );
    } elseif(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
      $wpdb->query(
        $wpdb->prepare("DELETE FROM $wpdb->banned_hosts WHERE id = %d",$_GET['id'])
      );
    } elseif(isset($_POST['banned_host'])) {
      $params = $_POST['banned_host'];
      $wpdb->query(
        $wpdb->prepare("INSERT INTO $wpdb->banned_hosts (host) VALUES(%s)",$params['host'])
      );
    }
    ?>
    <div class="wrap">
      <h2>Google Referrer Checker</h2>
      
      <h3 class="sub-option-menu">
        Referrers 
        <small>
        Show: (<a href="?page=referrer-checker&status=all">All</a>::<a href="?page=referrer-checker&status=2">Indexed</a>::<a href="?page=referrer-checker">Unindexed</a>::<a href="?page=referrer-checker&status=3">Invalid</a>)
        </small>
      </h3>
      <table class="form-table">  
        <th valign="top">
          <td>URI</td>
          <td>REF</td>
          <td>HOST</td>
          <td>STATUS</td>
          <td></td>
          <td></td>
        </th>
        <?php $items = $wpdb->get_results( $query ); foreach($items as $item): extract(parse_url($item->ref)); ?>
          <tr>
            <form method="post" action="?page=referrer-checker&id=<?php echo $item->id; ?>&status=<?php echo $status; ?>" class="referrer-checker">
              <?php settings_fields( 'referrer-checker' ); ?>
              
              <td><a href="<?php echo $item->uri; ?>"><?php echo $item->uri; ?></a></td>
              <td><a href="<?php echo $item->ref; ?>"><?php echo $item->ref; ?></a></td>
              <td><?php echo $host; ?></td>
              <td>
                <select name="ref_slot[status]">
                  <option value="1" <?php selected($item->status,1) ?>>Unindexed</option>
                  <option value="2" <?php selected($item->status,2) ?>>Indexed</option>
                  <option value="3" <?php selected($item->status,3) ?>>Invalid</option>
                </select>
              </td>
              <td>
                <input type="submit" value="Submit" />
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
      </table>
      
      
      <h3 class="sub-option-menu">Banned Hosts</h3>
      <table class="form-table">  
        <th valign="top">
          <td>HOST</td>
          <td></td>
        </th>
        <?php $items = $wpdb->get_results( "SELECT * FROM $wpdb->banned_hosts"); foreach($items as $item): ?>
          <tr>
            <form method="post" action="?page=referrer-checker&id=<?php echo $item->id; ?>&action=delete&status=<?php echo $status; ?>" class="referrer-checker">
              <?php settings_fields( 'referrer-checker' ); ?>
              <td><?php echo $item->host; ?></td>
              <td>
                <input type="submit" value="Delete" />
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
      </table>
      
      <h3 class="sub-option-menu">Ban A Host</h3>
      <form method="post" action="?page=referrer-checker&status=<?php echo $status; ?>" class="referrer-checker">
        <?php settings_fields( 'referrer-checker' ); ?>
        <table class="form-table">  
          <th valign="top">
            <td>HOST</td>
            <td></td>
          </th>
          <tr>          
            <td><input type="text" name="banned_host[host]" /></td>
            <td>
              <input type="submit" value="Ban Host" />
            </td>
          </tr>
        </table>
      </form>
      
    </div>
  <?php }
}

class GoogleReferrerChecker_Widget extends WP_Widget {
  function __construct() {
    parent::__construct( /* Base ID */'google_referrer_checker_widget', /* Name */'GoogleReferrerChecker_Widget', array( 'description' => 'A GoogleReferrerChecker Widget' ) );
  }


  function widget( $args, $instance ) {
    global $wpdb;
    
    extract( $args );
    
    $title = apply_filters( 'widget_title', $instance['title'] );
    $items = $wpdb->get_results( "SELECT * FROM $wpdb->ref_slots WHERE status=1 ORDER BY id DESC");
    ?>
    
    <?php if(is_array($items) && count($items) > 0): ?>
      <?php echo $before_widget; ?>
      <?php if ( $title ): ?>
        <?php echo $before_title . $title . $after_title; ?>
      <?php endif; ?>
      <ul>
        <?php foreach($items as $item): extract(parse_url($item->ref)); ?>
          <li><a href="<?php echo $item->ref ?>"><?php echo $host; ?></a></li>
        <?php endforeach; ?>
      </ul>
      <?php echo $after_widget; ?>
    <?php endif; ?>
    <?
  }
  
  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    
    return $instance;
  }

  /** @see WP_Widget::form */
  function form( $instance ) {
    if ( $instance ) {
      $title = esc_attr( $instance[ 'title' ] );
    }
    else {
      $title = __( 'New title', 'text_domain' );
    }
    ?>
    <p>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>
    <?php 
  }

} // class Foo_Widget


register_activation_hook( __FILE__, array('GoogleReferrerChecker', 'activate') );
register_activation_hook( __FILE__, array('GoogleReferrerChecker', 'deactivate') );
register_activation_hook( __FILE__, array('GoogleReferrerChecker', 'uninstall') );

add_action('plugins_loaded', array('GoogleReferrerChecker', 'update_db_check') );
add_action('plugins_loaded', array('GoogleReferrerChecker', 'setup') );
add_action('wp_head', array('GoogleReferrerChecker', 'random_update') );
add_action('wp_head', array('GoogleReferrerChecker', 'add_new') );

add_action('widgets_init',function(){
  return register_widget('GoogleReferrerChecker_Widget');
});