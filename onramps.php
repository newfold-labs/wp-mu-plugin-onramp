<?php
add_action('all_plugins', 'modify_plugins_list', 10, 1);
add_filter('plugin_action_links', 'remove_delete_button', 10, 2);
add_action('activated_plugin', 'store_brand_plugin_name_on_activation', 10, 1);
add_action('deactivated_plugin', 'store_brand_plugin_name_on_deactivation', 10, 1);
add_action('admin_init', 'check_and_install_plugin');

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


/**
 * Add placeholder for the brand plugin if it is not there in the plugin list 
 */
function modify_plugins_list ($plugins) {
    $add_plugin_placeholder = true;
    $brand_plugin = get_option( 'nfd_brand_plugin' );

    foreach( $plugins as $plugin=>$plugin_obj ) {
        if( "The Hostgator Plugin" === $brand_plugin && "wp-plugin-hostgator/wp-plugin-hostgator.php" === $plugin ){
            $add_plugin_placeholder = false;
        }
        else if( "The Bluehost Plugin" === $brand_plugin && "bluehost-wordpress-plugin/bluehost-wordpress-plugin.php" === $plugin ){
            $add_plugin_placeholder = false;
        }
    }

    if($add_plugin_placeholder){
        if("The Bluehost Plugin" === $brand_plugin) {
            $plugins['bluehost-wordpress-plugin/bluehost-wordpress-plugin.php'] = array(
                "Name" => "The Bluehost Plugin",
                "TextDomain"=> "",
                "Author"    => "Bluehost",
                "Version" => "",
                "PluginURI" => "",
                "AuthorURI" => "",
                "Description" => ""
            );
        }
        else if("The Hostgator Plugin" === $brand_plugin) {
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
       
    }
    return $plugins;
}

/**
 * save brand plugin name on activation
 */
function store_brand_plugin_name_on_activation($plugin) {
    update_brand_plugin_name($plugin);
}

/**
 * save brand plugin name on deactivation
 */
function store_brand_plugin_name_on_deactivation($plugin) {
    update_brand_plugin_name($plugin);
}

function update_brand_plugin_name($plugin) {
    $current_brand_plugin = get_option('nfd_brand_plugin', '');

    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
    $plugin_name = $plugin_data['Name'];

    if ('The Bluehost Plugin' === $plugin_name || 'The Hostgator Plugin' === $plugin_name) {
        if (empty($current_brand_plugin) || $current_brand_plugin !== $plugin_name) {
            update_option('nfd_brand_plugin', $plugin_name);
        }
    }
}

/**
 * On click of activate install and activate the plugin
 */
function check_and_install_plugin() {
    if ( isset( $_GET['action'] ) && $_GET['action'] == 'activate' && isset( $_GET['plugin'] ) &&
        ( $_GET['plugin'] == 'bluehost-wordpress-plugin/bluehost-wordpress-plugin.php' || 
         $_GET['plugin'] == 'wp-plugin-hostgator/wp-plugin-hostgator.php' ) ) {

        $current_brand_plugin = get_option( 'nfd_brand_plugin', '' );

        if ( 'The Bluehost Plugin' === $current_brand_plugin ) {
            $plugin_slug      = 'bluehost-wordpress-plugin';
            $plugin_main_file = 'bluehost-wordpress-plugin.php';
            $plugin_zip_url   = 'https://hiive.cloud/workers/release-api/plugins/bluehost/bluehost-wordpress-plugin/download';
        } elseif ( 'The Hostgator Plugin' === $current_brand_plugin ) {
            $plugin_slug      = 'wp-plugin-hostgator';
            $plugin_main_file = 'wp-plugin-hostgator.php';
            $plugin_zip_url   = 'https://hiive.cloud/workers/release-api/plugins/newfold-labs/wp-plugin-hostgator/download';
        } else {
            wp_die('Invalid brand plugin specified.');
        }

        // Ensure the filesystem method is set to direct
        if ( !defined( 'FS_METHOD' ) ) {
            define( 'FS_METHOD', 'direct' );
        }

        // Initialize the WP_Filesystem API
        if ( !function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $creds = request_filesystem_credentials( site_url() . '/wp-admin/', '', false, false, array() );
        if ( !WP_Filesystem( $creds ) ) {
            wp_die( 'Failed to initialize WP_Filesystem.' );
        }

        global $wp_filesystem;

        // Download the plugin zip file
        $tmp_file = download_url( $plugin_zip_url );
        if ( is_wp_error( $tmp_file ) ) {
            wp_die( 'Failed to download plugin: ' . $tmp_file->get_error_message() );
        }

        // Create a temporary directory for unzipping
        $tmp_dir = WP_CONTENT_DIR . '/tmp-' . uniqid();
        if ( !$wp_filesystem->mkdir( $tmp_dir ) ) {
            wp_die( 'Failed to create temporary directory.' );
        }

        // Unzip the plugin to the temporary directory
        $result = unzip_file( $tmp_file, $tmp_dir );
        if (is_wp_error( $result )) {
            wp_die( 'Failed to unzip plugin: ' . $result->get_error_message() );
        }
        $wp_filesystem->delete( $tmp_file );

        // Move the unzipped files to the plugins directory
        $unzipped_files = array_diff( scandir( $tmp_dir ), array( '.', '..' ) );
        if ( count( $unzipped_files ) == 1 && is_dir( $tmp_dir . '/' . reset($unzipped_files ) ) ) {
            // If there's a single directory inside the temporary directory, move its contents
            $unzipped_dir = $tmp_dir . '/' . reset($unzipped_files);
            $wp_filesystem->move( $unzipped_dir, WP_PLUGIN_DIR . '/' . $plugin_slug );
        } else {
            // Otherwise, move the entire temporary directory
            $wp_filesystem->move( $tmp_dir, WP_PLUGIN_DIR . '/' . $plugin_slug );
        }
        $wp_filesystem->delete( $tmp_dir );

        // Verify the plugin path
        $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_slug . '/' . $plugin_main_file;
        if ( !$wp_filesystem->exists( $plugin_path ) ) {
            wp_die( 'The plugin main file does not exist at: ' . $plugin_path );
        }

        // Check the contents of the plugin main file
        $plugin_file_contents = $wp_filesystem->get_contents( $plugin_path );
        if ( $plugin_file_contents === false ) {
            wp_die( 'Failed to read the plugin main file.' );
        }

        // Ensure the plugin main file contains the required header
        if ( !preg_match( '/Plugin Name:\s*(.+)/', $plugin_file_contents ) ) {
            wp_die( 'The plugin main file does not contain a valid header.' );
        }

        // Activate the plugin
        $activate_result = activate_plugin( $plugin_slug . '/' . $plugin_main_file );
        if ( is_wp_error( $activate_result ) ) {
            wp_die( 'Failed to activate plugin: ' . $activate_result->get_error_message() );
        }

        // Redirect to the plugins page
        wp_redirect( admin_url( 'plugins.php?activate=true' ) );
        exit;
    }
}