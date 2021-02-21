<?php
/**
 * Plugin Name: Synchronize Editor and ACF Color Pickers
 * Plugin URI: https://github.com/MarieComet/sync-editor-acf-color-pickers
 * Description: This plugin synchronize the Advanced Custom Fields color picker field with the editor (gutenberg) color picker.
 * Author: Marie Comet
 * Author URI: https://profiles.wordpress.org/chaton666/
 * Version: 0.0.1
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain: sync-editor-acf-color-pickers
 * Domain Path: /languages/
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! defined( 'SEACP_URL' ) ) {
    define( 'SEACP_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'SEACP_PATH' ) ) {
    define( 'SEACP_PATH', plugin_dir_path( __FILE__ ) );
}

/**
 * Register activation hook.
 * @since 0.0.1
 */
register_activation_hook( __FILE__, 'seacp_activation_hook' );
function seacp_activation_hook() {
    /* Create transient data */
    set_transient( 'seacp-admin-notice', true, 5 );
    set_transient( 'seacp-version-admin-notice', true, 5 );
}


/**
 * Admin Notice on Activation.
 * @since 0.0.1
 */
add_action( 'admin_notices', 'seacp_missing_notice' );
function seacp_missing_notice(){

    // color_picker_args filter was introduced in ACF 5.3.6
    $outdated_version = true;

    if ( defined( 'ACF_VERSION' ) && version_compare( ACF_VERSION, '5.3.6' ) >= 0 ) {
        $outdated_version = false;
    }
 
    /* Check transient, if available display notice */
    if ( get_transient( 'seacp-admin-notice' ) && ! class_exists('acf') ) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e( 'Sync Editor ACF Color Pickers plugin requires "Advanced Custom Fields" 5.3.6 or higher to run. Please download and activate it', 'sync-editor-acf-color-pickers' ); ?></p>
        </div>
        <?php
        /* Delete transient, only display this notice once. */
        delete_transient( 'seacp-admin-notice' );
    }

    if ( get_transient( 'seacp-version-admin-notice' ) && class_exists( 'acf' ) && $outdated_version ) {

        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e( 'Sync Editor ACF Color Pickers plugin requires at least "Advanced Custom Fields version 5.3.6" to run. Please update ACF.', 'sync-editor-acf-color-pickers' ); ?></p>
        </div>
        <?php
        /* Delete transient, only display this notice once. */
        delete_transient( 'seacp-version-admin-notice' );
    }
}

/**
 * Helper : transform colors array of key/value to values
 * @since 0.0.1
 */
function seacp_get_theme_colors() {

    // Get colors palette registerd in theme support
    $color_palette = get_theme_support( 'editor-color-palette' );

    if ( ! empty( $color_palette ) ) {

        // Get each 'color' value (hex code)
        $colors = array_column( $color_palette[ 0 ], 'color' );
        return $colors;

    } else {

        return false;

    }

}

/**
 * Load plugin functions on plugins loaded
 * @since 0.0.1
 */
add_action( 'plugins_loaded', 'seacp_load' );
function seacp_load() {

    if ( ! class_exists( 'acf' ) ) {
        return;
    }

    load_plugin_textdomain( 'sync-editor-acf-color-pickers', false, SEACP_PATH . '/languages/' );

    if ( defined( 'ACF_VERSION' ) && version_compare( ACF_VERSION, '5.3.6' ) >= 0 ) {

        add_action('acf/input/admin_footer', 'seacp_acf_admin_footer');

    }

}

/**
 * Add theme color palette to ACF color picker
 * @since 0.0.1
 * Hooked on acf/input/admin_footer https://www.advancedcustomfields.com/resources/acf-input-admin_footer/
 */
function seacp_acf_admin_footer() {

    // Get colors palette registerd in theme support
    $colors = seacp_get_theme_colors();

    if ( $colors ) {
        ?>
        <script type="text/javascript">
        if ( window.acf ) {
            acf.add_filter( 'color_picker_args', function( args, field ) {
                args.palettes = <?php echo json_encode( $colors ); ?>;
                return args;
            } );
        }        
        </script>
        <?php
    }
    
}