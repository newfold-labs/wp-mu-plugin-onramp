<?php

add_filter( 'plugin_action_links', 'remove_delete_button', 10, 2 );
add_action( 'bulk_actions-plugins', 'prevent_bulk_plugin_deletion' );

/**
 * Remove the delete button for brand plugin
 *
 * @param array  $actions An array of plugin action links
 *
 * @param string $plugin_file Path to the plugin
 */
function remove_delete_button( $actions, $plugin_file ) {
	// List of plugins to protect from deletion
	$protected_plugins = array(
		'wp-plugin-hostgator/wp-plugin-hostgator.php',
		'bluehost-wordpress-plugin/bluehost-wordpress-plugin.php',
	);
	if ( in_array( $plugin_file, $protected_plugins ) ) {
		unset( $actions['delete'] );
	}
	return $actions;
}

/**
 * Restrict the bulk deletion of plugins
 *
 * @param array $actions An array of plugin action links
 */
function prevent_bulk_plugin_deletion( $actions ) {
	unset( $actions['delete-selected'] );
	return $actions;
}
