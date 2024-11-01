<?php
/**
 * Plugin Name:       GutSlider - All in One Slider Blocks
 * Description:       A collection of custom Gutenberg Slider Blocks to slide your content.
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Version:           2.7.6
 * Author:            Zakaria Binsaifullah
 * Author URI:        https://makegutenblock.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       slider-blocks
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

/**
 * Blocks Final Class
 * return GutSliderBlocks
 */
 if( ! class_exists( 'GutSliderBlocks' ) ) {

	final class GutSliderBlocks {

		private static $instance = null;

		/**
		 * The constructor function initializes constants, includes necessary files, loads a text domain, and
		 * sets up activation redirection for a PHP class.
		 */
		private function __construct() {
			$this->define_constants();
			$this->includes();
			add_action( 'init', [ $this, 'load_textdomain' ] );
			register_activation_hook( __FILE__, [ $this, 'set_activation_redirect' ] );
			add_action( 'admin_init', [ $this, 'redirect_to_welcome_page' ] );
		}
	
		/**
		 * Define the plugin constants
		 * return void
		 */
		private function define_constants() {
			define( 'GUTSLIDER_VERSION', '2.7.6' );
			define( 'GUTSLIDER_URL', plugin_dir_url( __FILE__ ) );	
			define('GUTSLIDER_DIR_PATH', plugin_dir_path(__FILE__));
			define( 'GUTSLIDER_DIR', __DIR__ );
		}

		/**
		 * Initialize the plugin
		 * return GutSliderBlocks
		 */
		public static function init() {
			if( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Include the files
		 * return void
		 */
		public function includes() {
			require_once GUTSLIDER_DIR . '/includes/init.php';
			require_once GUTSLIDER_DIR . '/admin/admin.php';
		}

		/**
		 * Load the plugin text domain
		 * return void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'slider-blocks', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Set the activation redirect
		 * return void
		 */
		public function set_activation_redirect() {
			add_option( 'gutsliderblocks_do_activation_redirect', true );
		}

		/**
		 * Redirect to the welcome page
		 * return void
		 */
		public function redirect_to_welcome_page() {
			if ( is_admin() && get_option( 'gutsliderblocks_do_activation_redirect', false ) ) {
				delete_option( 'gutsliderblocks_do_activation_redirect' );
				wp_safe_redirect( admin_url( 'admin.php?page=gutslider-blocks' ) );
				exit; // Added exit after redirect
			}
		} 
		
		/**
		 * Cleanup the plugin data
		 * return void
		 */
		public static function cleanup() {
			$upload_dir = wp_upload_dir();
            $css_dir = $upload_dir['basedir'] . '/gutslider-styles';

            if ( file_exists( $css_dir ) ) {
                global $wp_filesystem;
                if ( empty( $wp_filesystem ) ) {
                    require_once( ABSPATH . '/wp-admin/includes/file.php' );
                    WP_Filesystem();
                }

                $files = glob( $css_dir . '/*' );
                foreach ( $files as $file ) {
                    if ( is_file( $file ) ) {
                        $wp_filesystem->delete( $file );
                    }
                }
                $wp_filesystem->rmdir( $css_dir );
            }

            // Remove parent directory if it's empty
            $parent_dir = dirname( $css_dir );
            if ( file_exists( $parent_dir ) && ( count( glob( "$parent_dir/*" ) ) === 0 ) ) {
                $wp_filesystem->rmdir( $parent_dir );
            }
		}

	}

 }

 /**
  * Initialize the GutSliderBlocks
  * return GutSliderBlocks
  */
  function gutsliderblocks() {
	  return GutSliderBlocks::init();
  }

  // kick-off the plugin
  gutsliderblocks();