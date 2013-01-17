<?php
/*
Plugin Name: Sparks Events
Plugin URI: http://starverte.com/plugins/sparks-events
Description: Part of the Sparks Framework. A plugin that adds the ability of an events calendar.
Version: alpha
Author: Star Verte LLC
Author URI: http://www.starverte.com
License: GPL2

  Copyright 2012  Star Verte LLC  (email : info@starverte.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function sparks_events_init() {
  $labels = array(
    'name' => 'Events',
    'singular_name' => 'Event',
    'add_new' => 'Add New',
    'add_new_item' => 'Add New Event',
    'edit_item' => 'Edit Event',
    'new_item' => 'New Event',
    'all_items' => 'All Events',
    'view_item' => 'View Event',
    'search_items' => 'Search Events',
    'not_found' =>  'No events found',
    'not_found_in_trash' => 'No events found in Trash. Did you check recycling?', 
    'parent_event_colon' => '',
    'menu_name' => 'Events'
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' => array( 'slug' => 'events' ),
    'capability_type' => 'post',
    'has_archive' => true, 
    'hierarchical' => false,
    'menu_position' => 5,
    'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
  ); 

  register_post_type( 'sp_event', $args );
}
add_action( 'init', 'sparks_events_init' );

//add filter to ensure the text Event, or event, is displayed when user updates an event 

function codex_sp_event_updated_messages( $messages ) {
  global $post, $post_ID;

  $messages['sp_event'] = array(
    0 => '', // Unused. Messages start at index 1.
    1 => sprintf( __('Event updated. <a href="%s">View event</a>'), esc_url( get_permalink($post_ID) ) ),
    2 => __('Custom field updated.'),
    3 => __('Custom field deleted.'),
    4 => __('Event updated.'),
    /* translators: %s: date and time of the revision */
    5 => isset($_GET['revision']) ? sprintf( __('Event restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => sprintf( __('Event published. <a href="%s">View event</a>'), esc_url( get_permalink($post_ID) ) ),
    7 => __('Event saved.'),
    8 => sprintf( __('Event submitted. <a target="_blank" href="%s">Preview event</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
    9 => sprintf( __('Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>'),
      // translators: Publish box date format, see http://php.net/date
      date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
    10 => sprintf( __('Event draft updated. <a target="_blank" href="%s">Preview event</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
  );

  return $messages;
}
add_filter( 'post_updated_messages', 'codex_sp_event_updated_messages' );

// BEGIN - Create custom fields
add_action( 'add_meta_boxes', 'sp_event_add_custom_boxes' );

function sp_event_add_custom_boxes() {
	add_meta_box('sp_event_meta', 'Details', 'sp_event_meta', 'sp_event', 'side', 'high');
}

/* Staff Details */
function sp_event_meta() {
	global $post;
	$custom = get_post_custom($post->ID);
    	$event_loc = $custom["event_loc"] [0];
	$event_start = $custom["event_start"] [0];
	$event_end = $custom["event_end"] [0];
	
?>
    <p><label>Location</label> 
	<input type="text" size="10" name="event_loc" value="<?php echo $event_loc; ?>" /></p>
    <p><label>Starts</label> 
	<input class="datepicker" type="text" size="10" name="event_start" value="<?php echo date( 'F j, Y g:i a', $event_start ) ?>" /></p>
    <p><label>Ends</label> 
	<input class="datepicker" type="text" size="10" name="event_end" value="<?php echo date( 'F j, Y g:i a', $event_end ) ?>" /></p>
	<?php
}

/* Save Details */
add_action('save_post', 'save_event_details');


function save_event_details(){
  global $post;
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
	return $post_id;
  }

  if( defined('DOING_AJAX') && DOING_AJAX ) { //Prevents the metaboxes from being overwritten while quick editing.
	return $post_id;
  }

  if( ereg('/\edit\.php', $_SERVER['REQUEST_URI']) ) { //Detects if the save action is coming from a quick edit/batch edit.
	return $post_id;
  }
  // save all meta data
  update_post_meta($post->ID, "event_loc", $_POST["event_loc"]);
  update_post_meta($post->ID, "event_start", strtotime($_POST["event_start"]));
  update_post_meta($post->ID, "event_end", strtotime($_POST["event_end"]));  
  
}
// END - Custom Fields

//Load datepicker
function events_admin_init() {
        wp_register_script( 'events-datepicker', plugins_url('/datepicker.js', __FILE__) );
        wp_enqueue_script( 'events-datepicker' );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_register_script( 'jquery-base', 'http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css' );
        wp_enqueue_style( 'jquery-base' );
}
add_action( 'admin_enqueue_scripts', 'events_admin_init' );

?>
