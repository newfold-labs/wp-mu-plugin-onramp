<?php

add_action('all_plugins', 'modify_plugins_list', 10, 1);
add_filter('plugin_action_links', 'custom_plugin_action_links', 10, 2);
add_action('admin_post_custom_plugin_activate', 'custom_activate_plugin');

function custom_plugin_action_links($actions, $plugin_file) {
    // Replace 'your-plugin-folder/your-plugin-file.php' with the path to your plugin's main file
    $target_plugin = 'bluehost-wordpress-plugin/bluehost-wordpress-plugin.php';

    // Check if the plugin file matches the target plugin
    if ($plugin_file === $target_plugin) {
        // Remove the delete action link
        // unset($actions['delete']);
        unset($actions['delete']);
        // unset($actions['activate']);
        // $actions['custom_activate'] = '<a href="' . wp_nonce_url(admin_url('admin-post.php?action=custom_plugin_activate'), 'custom_plugin_activate') . '">Activate</a>';
        // $actions['activate'] = "activated_plugin";
    }

    return $actions;
}

function modify_plugins_list ($plugins) {
    $add_plugin_placeholder = true;
    foreach( $plugins as $plugin=>$plugin_obj ) {
        // if( "bluehost-wordpress-plugin/bluehost-wordpress-plugin.php" === $plugin ){
        //     $add_plugin_placeholder = false;
        // }else 
         if( "wp-plugin-hostgator/wp-plugin-hostgator.php" === $plugin ){
            $add_plugin_placeholder = false;
        }
    }
    if($add_plugin_placeholder){
        $plugins['wp-plugin-hostgator/wp-plugin-hostgator.php'] = array(
            "Name" => "The Hostgator Plugin",
            "TextDomain"=> "",
            "Author"    => "Hostgator",
            "Version" => "",
            "PluginURI" => "",
            "AuthorURI" => "",
            "Description" => ""
        );
    }
    return $plugins;
}

function custom_activate_plugin() {
$upgrader = new Plugin_Upgrader();
$install_result = $upgrader->install("https://hiive.cloud/workers/release-api/plugins/newfold-labs/wp-plugin-hostgator/download");
 // Deactivate output buffering
// Activate the plugin after installation
$plugin = 'wp-plugin-hostgator/wp-plugin-hostgator.php';
\activate_plugin($plugin);
// $active_plugins = get_option('active_plugins', []);
// update_option('active_plugins', array_merge($active_plugins, array($plugin)));
// Redirect back to the plugins page after installation and activation
// wp_safe_redirect(admin_url('plugins.php'));
// exit;
echo '<script>window.location.href="' . admin_url('plugins.php') . '";</script>';

exit;
}