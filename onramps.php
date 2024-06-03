<?php

add_action('all_plugins', 'modify_plugins_list', 10, 1);

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