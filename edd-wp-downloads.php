<?php
/**
 * Plugin Name:     EDD WordPress.org Downloads
 * Plugin URI:      https://expandedfronts.com
 * Description:     Allows you to add plugins and themes from WordPress.org to Easy Digital Downloads.
 * Version:         1.0.0
 * Author:          Expanded Fronts, LLC
 * Author URI:      https://expandedfronts.com
 * Text Domain:     edd-wp-downloads
 *
 * @package         EDD\EDD_WP_Downloads
 * @author          Expanded Fronts, LLC
 * @copyright       Copyright (c) Expanded Fronts, LLC
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'EDD_WP_Downloads' ) ) {

    /**
     * Main EDD_WP_Downloads class
     *
     * @since       1.0.0
     */
    class EDD_WP_Downloads {

        /**
         * @var         EDD_WP_Downloads $instance The one true EDD_WP_Downloads
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true EDD_WP_Downloads
         */
        public static function instance() {
            if( ! self::$instance ) {
                self::$instance = new EDD_WP_Downloads();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'EDD_WP_DOWNLOADS_VER', '1.0.0' );

            // Plugin path
            define( 'EDD_WP_DOWNLOADS_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_WP_DOWNLOADS_URL', plugin_dir_url( __FILE__ ) );
        }


        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            // Include scripts
            require_once EDD_WP_DOWNLOADS_DIR . 'includes/functions.php';
            require_once EDD_WP_DOWNLOADS_DIR . 'includes/widgets.php';
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = EDD_WP_DOWNLOADS_DIR . '/languages/';
            $lang_dir = apply_filters( 'edd_wp_downloads_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'edd-wp-downloads' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'edd-wp-downloads', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/edd-wp-downloads/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-wordpress-downloads/ folder
                load_textdomain( 'edd-wp-downloads', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-wordpress-downloads/languages/ folder
                load_textdomain( 'edd-wp-downloads', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'edd-wp-downloads', false, $lang_dir );
            }
        }

    }

} // End of if class_exists check


/**
 * The main function responsible for returning the one true EDD_WP_Downloads
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \EDD_WP_Downloads The one true EDD_WP_Downloads
 */
function EDD_WP_Downloads_load() {
    if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
        if( ! class_exists( 'EDD_Extension_Activation' ) ) {
            require_once 'includes/class.extension-activation.php';
        }

        $activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();
    } else {
        return EDD_WP_Downloads::instance();
    }
}
add_action( 'plugins_loaded', 'EDD_WP_Downloads_load' );
