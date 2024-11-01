<?php
/**
 * Plugin Admin Page 
 * @package GutSliderBlocks
 */	

if (!defined('ABSPATH')) exit;

if (!class_exists('GutSlider_Admin')) {
    class GutSlider_Admin {
        /**
         * Slider block settings
         */
        private const BLOCK_SETTINGS = [
            'gut_fixed_content_slider',
            'gut_any_content_slider',
            'gut_testimonial_slider',
            'gut_post_slider',
            'gut_photo_carousel',
            'gut_logo_carousel',
            'gut_before_after_slider',
            'gut_videos_carousel'
        ];

        /**
         * Constructor
         */
        public function __construct() {
            add_action('admin_menu', [$this, 'admin_menu']);
            add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
            add_action('admin_init', [$this, 'initialize_admin']);
            add_action('rest_api_init', [$this, 'register_settings']);
        }

        /**
         * Initialize admin functionality
         */
        public function initialize_admin() {
            $this->register_settings();
            $this->include_data_sdk();
        }
        
        /**
         * Enqueue admin scripts and styles
         * @param string $screen Current admin screen
         */
        public function admin_assets($screen) {
            if ($screen !== 'toplevel_page_gutslider-blocks') {
                return;
            }

            $this->enqueue_admin_assets();
        }
    
        /**
         * Add admin menu
         */
        public function admin_menu() {
            add_menu_page(
                __('GutSlider', 'slider-blocks'),
                __('GutSlider', 'slider-blocks'),
                'manage_options',
                'gutslider-blocks',
                [$this, 'render_admin_page'],
                'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjI0IiBoZWlnaHQ9IjI0IiByeD0iMTIiIGZpbGw9IiNEOUQ5RDkiLz4KPHBhdGggZD0iTTE1IDdIMTEuODIzNUg5VjE3SDE1VjEyLjQxNjdIMTIuODgyNEgxMS44MjM1IiBzdHJva2U9IiMxRDIzMjciIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+Cjwvc3ZnPgo=',
                100
            );
        }
    
        /**
         * Render admin page
         */
        public function render_admin_page() {
            echo '<div id="gutslider"></div>';
        }

        /**
         * Register block settings
         */
        public function register_settings() {
            foreach (self::BLOCK_SETTINGS as $setting) {
                register_setting('rest-api-settings', $setting, [
                    'type' => 'boolean',
                    'default' => true,
                    'show_in_rest' => true,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ]);
            }
        }

        /**
         * Include data SDK
         */
        private function include_data_sdk() {
            require_once dirname(__FILE__) . '/dci/start.php';
            
            dci_dynamic_init([
                'sdk_version' => '1.2.1',
                'product_id' => 3,
                'plugin_name' => 'GutSlider',
                'plugin_title' => 'GutSlider',
                'api_endpoint' => 'https://dashboard.codedivo.com/wp-json/dci/v1/data-insights',
                'slug' => 'gutslider-blocks',
                'core_file' => false,
                'plugin_deactivate_id' => false,
                'menu' => ['slug' => 'gutslider-blocks'],
                'public_key' => 'pk_KxU4qcYXPyqvBDwsyyBkbCfY9Gulc1z5',
                'is_premium' => false,
                'popup_notice' => false,
                'deactivate_feedback' => true,
                'delay_time' => ['time' => 3 * DAY_IN_SECONDS],
                'text_domain' => 'slider-blocks',
                'plugin_msg' => '<p>Thank you for using GutSlider! 🎉</p><p>We collect some non-sensitive data to improve our product and decide which features to build next.</p>',
            ]);
        }

        /**
         * Get GutSlider changelogs
         * @return array|false Changelog data or false on failure
         */
        private function get_change_logs() {
            $changelog_file = GUTSLIDER_DIR . '/changelogs.json';
            
            if (!file_exists($changelog_file)) {
                return false;
            }
            
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once(ABSPATH . '/wp-admin/includes/file.php');
                WP_Filesystem();
            }
            
            $changelogs = $wp_filesystem->get_contents($changelog_file);
            return json_decode($changelogs, true);
        }

        /**
         * Enqueue admin assets
         */
        private function enqueue_admin_assets() {
            $dependency_file = GUTSLIDER_DIR . '/build/admin/admin.asset.php';
            $dependencies = file_exists($dependency_file) ? require_once($dependency_file) : [];

            wp_enqueue_style('gutslider-admin-style', GUTSLIDER_URL . 'build/admin/style-admin.css', [], GUTSLIDER_VERSION);
            wp_enqueue_script('gutslider-admin-script', GUTSLIDER_URL . 'build/admin/admin.js', $dependencies['dependencies'], GUTSLIDER_VERSION, true);
            wp_enqueue_style('wp-components');

            wp_localize_script('gutslider-admin-script', 'gutslider', [
                'version' => GUTSLIDER_VERSION,
                'changeLogs' => $this->get_change_logs()
            ]);
        }
    }
}

new GutSlider_Admin();