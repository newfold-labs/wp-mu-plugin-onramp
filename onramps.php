<?php

add_filter('plugin_action_links', 'remove_delete_button', 10, 2);

/**
 * Remove the delete button for brand plugin
 */
function remove_delete_button($actions, $plugin_file) {
    $BH_plugin = 'bluehost-wordpress-plugin/bluehost-wordpress-plugin.php';
    $HG_plugin = 'wp-plugin-hostgator/wp-plugin-hostgator.php';

    if ($plugin_file === $BH_plugin || $plugin_file === $HG_plugin) {
        unset($actions['delete']);
    }

    return $actions;
}