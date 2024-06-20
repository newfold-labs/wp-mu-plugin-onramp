<?php

add_filter( 'plugin_action_links', 'remove_delete_button', 10, 2 );
add_action( 'bulk_actions-plugins', 'prevent_bulk_plugin_deletion' );

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
 * Restrict the bulk deletion of plugins
 */
function prevent_bulk_plugin_deletion( $actions ) {
    error_log(json_encode($actions));
    unset( $actions['delete-selected'] );
    return $actions;
}
