<?php
add_action( 'all_plugins', 'modify_plugins_list', 10, 1 );
add_filter( 'plugin_action_links', 'remove_delete_button', 10, 2 );
add_action( 'activated_plugin', 'store_brand_plugin_name_on_activation', 10, 1 );
add_action( 'deactivated_plugin', 'store_brand_plugin_name_on_deactivation', 10, 1 );
add_action( 'bulk_actions-plugins', 'prevent_bulk_plugin_deletion' );
add_action( 'admin_post_custom_plugin_activate', 'custom_activate_plugin' );

/**
 * Remove the delete button for brand plugin
 *
 * @param array  $actions An array of plugin action links
 *
 * @param string $plugin_file Path to the plugin
 */
function remove_delete_button( $actions, $plugin_file ) {
    // Define the path to the plugins directory
    $plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin_file );
	// List of plugins to protect from deletion
	$protected_plugins = array(
		'wp-plugin-hostgator/wp-plugin-hostgator.php',
		'bluehost-wordpress-plugin/bluehost-wordpress-plugin.php',
	);
	if ( in_array( $plugin_file, $protected_plugins ) ) {
		unset( $actions['delete'] );
        if ( !file_exists( $plugin_dir ) || !file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
            // Folder or main file does not exist, show custom activate link
            unset( $actions['activate'] );
            $actions['custom_activate'] = '<a href="' . wp_nonce_url( admin_url( 'admin-post.php?action=custom_plugin_activate' ), 'custom_plugin_activate' ) . '">Activate--</a>';
        } 
	}
	return $actions;
}

/**
 * Add placeholder for the brand plugin if it is not there in the plugin list 
 */
function modify_plugins_list ( $plugins ) {
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
        if( "The Bluehost Plugin" === $brand_plugin ) {
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
        else if( "The Hostgator Plugin" === $brand_plugin ) {
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
function store_brand_plugin_name_on_activation( $plugin ) {
    update_brand_plugin_name( $plugin );
}

/**
 * save brand plugin name on deactivation
 */
function store_brand_plugin_name_on_deactivation( $plugin ) {
    update_brand_plugin_name( $plugin );
}

function update_brand_plugin_name( $plugin ) {
    $current_brand_plugin = get_option( 'nfd_brand_plugin', '' );

    $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
    $plugin_name = $plugin_data['Name'];

    if ( 'The Bluehost Plugin' === $plugin_name || 'The Hostgator Plugin' === $plugin_name ) {
        if ( empty( $current_brand_plugin ) || $current_brand_plugin !== $plugin_name ) {
            update_option( 'nfd_brand_plugin', $plugin_name );
        }
    }
}

/**
 * On click of activate install and activate the plugin
 */
function custom_activate_plugin() {

    if ( !current_user_can( 'install_plugins' ) ) {
        wp_die( __( 'You do not have sufficient permissions to install plugins.' ) );
    }
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    ob_start();
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
        wp_die( 'Invalid brand plugin specified.' );
    }

    $upgrader = new Plugin_Upgrader();
    $install_result = $upgrader->install( $plugin_zip_url );
    if (is_wp_error( $install_result )) {
        wp_die( $install_result );
    }
    
    $activate_result = activate_plugin( $plugin_slug . '/' . $plugin_main_file );
    if ( is_wp_error( $activate_result ) ) {
        error_log( 'Activation error: ' . $activate_result->get_error_message() );
        wp_die( 'Failed to activate plugin: ' . $activate_result->get_error_message() );
    }
    
    ob_end_clean();
    error_log( 'Redirecting to plugins page' );
    if ( !headers_sent() ) {
        wp_redirect( admin_url( 'plugins.php?activate=true' ), 302 );
        exit;
    } else {
        error_log( 'Headers already sent, cannot redirect' );
        echo '<script type="text/javascript">window.location.href="' . admin_url( 'plugins.php?activate=true' ) . '";</script>';
        exit;
    }
}
/**
 * Restrict the bulk deletion of plugins
 *
 * @param array $actions An array of plugin action links
 * */
function prevent_bulk_plugin_deletion( $actions ) {
	unset( $actions['delete-selected'] );
	return $actions;
}
