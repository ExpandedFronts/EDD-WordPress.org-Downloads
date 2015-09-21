<?php
/**
 * Plugin Name:     Easy Digital Downloads - WordPress Plugin Downloads
 * Plugin URI:      https://expandedfronts.com
 * Description:     Allows you to list downloads as a free plugin on WordPress.org.
 * Version:         1.0.0
 * Author:          Expanded Fronts, LLC
 * Author URI:      https://expandedfronts.com
 * Text Domain:     edd-wordpress-plugins
 *
 * @package         EDD\EDD_WordPress_Plugins
 * @author          @todo
 * @copyright       Copyright (c) @todo
 *
 * IMPORTANT! Ensure that you make the following adjustments
 * before releasing your extension:
 *
 * - Replace all instances of plugin-name with the name of your plugin.
 *   By WordPress coding standards, the folder name, plugin file name,
 *   and text domain should all match. For the purposes of standardization,
 *   the folder name, plugin file name, and text domain are all the
 *   lowercase form of the actual plugin name, replacing spaces with
 *   hyphens.
 *
 * - Replace all instances of Plugin_Name with the name of your plugin.
 *   For the purposes of standardization, the camel case form of the plugin
 *   name, replacing spaces with underscores, is used to define classes
 *   in your extension.
 *
 * - Replace all instances of PLUGIN_NAME with the name of your plugin.
 *   For the purposes of standardization, the uppercase form of the plugin
 *   name, removing spaces, is used to define plugin constants.
 *
 * - Replace all instances of Plugin Name with the actual name of your
 *   plugin. This really doesn't need to be anywhere other than in the
 *   EDD Licensing call in the hooks method.
 *
 * - Find all instances of @todo in the plugin and update the relevant
 *   areas as necessary.
 *
 * - All functions that are not class methods MUST be prefixed with the
 *   plugin name, replacing spaces with underscores. NOT PREFIXING YOUR
 *   FUNCTIONS CAN CAUSE PLUGIN CONFLICTS!
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'EDD_WordPress_Plugins' ) ) {

    /**
     * Main EDD_WordPress_Plugins class
     *
     * @since       1.0.0
     */
    class EDD_WordPress_Plugins {

        /**
         * @var         EDD_WordPress_Plugins $instance The one true EDD_WordPress_Plugins
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true EDD_WordPress_Plugins
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new EDD_WordPress_Plugins();
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
            define( 'EDD_WORDPRESS_PLUGINS_VER', '1.0.0' );

            // Plugin path
            define( 'EDD_WORDPRESS_PLUGINS_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_WORDPRESS_PLUGINS_URL', plugin_dir_url( __FILE__ ) );
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
            require_once EDD_WORDPRESS_PLUGINS_DIR . 'includes/functions.php';
            require_once EDD_WORDPRESS_PLUGINS_DIR . 'includes/widgets.php';
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
            $lang_dir = EDD_WORDPRESS_PLUGINS_DIR . '/languages/';
            $lang_dir = apply_filters( 'edd_wordpress_plugins_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'edd-wordpress-plugins' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'edd-wordpress-plugins', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/edd-wordpress-plugins/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-wordpress-plugins/ folder
                load_textdomain( 'edd-wordpress-plugins', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-wordpress-plugins/languages/ folder
                load_textdomain( 'edd-wordpress-plugins', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'edd-wordpress-plugins', false, $lang_dir );
            }
        }

    }
} // End if class_exists check


/**
 * The main function responsible for returning the one true EDD_Plugin_Name
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \EDD_Plugin_Name The one true EDD_Plugin_Name
 *
 * @todo        Inclusion of the activation code below isn't mandatory, but
 *              can prevent any number of errors, including fatal errors, in
 *              situations where your extension is activated but EDD is not
 *              present.
 */
function EDD_WordPress_Plugins_load() {
    if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
        if( ! class_exists( 'EDD_Extension_Activation' ) ) {
            require_once 'includes/class.extension-activation.php';
        }

        $activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();
    } else {
        return EDD_WordPress_Plugins::instance();
    }
}
add_action( 'plugins_loaded', 'EDD_WordPress_Plugins_load' );
