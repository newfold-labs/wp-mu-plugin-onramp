<?php

add_filter( 'plugin_action_links', 'remove_delete_button', 10, 2 );
add_action( 'admin_init', 'prevent_bulk_plugin_deletion' );

 // List of plugins to protect from deletion
const protected_plugins = array(
    'wp-plugin-hostgator/wp-plugin-hostgator.php',
    'bluehost-wordpress-plugin/bluehost-wordpress-plugin.php' 
);

/**
 * Remove the delete button for brand plugin
 */
function remove_delete_button( $actions, $plugin_file ) {
    if ( in_array( $plugin_file, protected_plugins ) ) {
        unset( $actions['delete'] );
    }
    return $actions;
}

/**
 * Restrict the deletion of brand plugin from bulk options
 */
function prevent_bulk_plugin_deletion() {
    if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'delete-plugin' ) {
        // List of plugins to protect from deletion


            $plugin_file = $_REQUEST['plugin'];
            if ( in_array( $plugin_file, protected_plugins ) ) {
                wp_die("You cannot delete the plugin: ". ucwords( str_replace( "-", " ", $_REQUEST["slug"] ) ) );
            }
      
    }
}
