<?php
/**
 * Generate Dynamic Style
 * @package GutSliderBlocks
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

if( ! class_exists( 'GutSlider_Dynamic_Style' ) ) {

    class GutSlider_Dynamic_Style {
        private $styles = [];
        private $upload_dir;
        private $upload_url;

        /**
         * Constructor
         */
        public function __construct() {
            add_filter( 'render_block', [ $this, 'collect_block_styles' ], 10, 2 );

            if( wp_is_block_theme(  ) ) {
                add_action( 'wp_enqueue_scripts', [ $this, 'generate_and_enqueue_combined_css' ] );
            } else {
                add_action( 'wp_footer', [ $this, 'generate_and_enqueue_combined_css' ] );
            }

            
            $upload_dir = wp_upload_dir();
            $this->upload_dir = $upload_dir['basedir'] . '/gutslider-styles/';
            $this->upload_url = $upload_dir['baseurl'] . '/gutslider-styles/';
            
            // Create directory if it doesn't exist
            if ( ! file_exists( $this->upload_dir ) ) {
                wp_mkdir_p( $this->upload_dir );
            }
        }

        /**
         * Collect styles from individual blocks
         */
        public function collect_block_styles($block_content, $block) {
            if (isset($block['blockName']) && str_contains($block['blockName'], 'gutsliders/')) {
                do_action( 'gutsliders_render_block', $block );

                if (isset($block['attrs']['blockStyle'])) {
                    $style = $block['attrs']['blockStyle'];
                    
                    if ( is_array( $style ) && !empty( $style ) ) {
                        $style = implode(' ', $style);
                    }
                    
                    $this->styles[] = $style;
                }
            }
            return $block_content;
        }

        /**
         * Generate and enqueue combined CSS
         */
        public function generate_and_enqueue_combined_css() {
            if (empty($this->styles)) {
                return;
            }

            $combined_css = implode("\n", $this->styles);
            $minified_css = $this->minify_css($combined_css);
            
            $css_file_name = 'gutslider-styles-' . get_the_ID() . '.min.css';
            $css_file_path = $this->upload_dir . $css_file_name;
            $css_file_url = $this->upload_url . $css_file_name;

            // Initialize the WP_Filesystem
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                WP_Filesystem();
            }

            // Only create a new file if it doesn't exist or the content has changed
            $existing_content = $wp_filesystem->exists($css_file_path) ? $wp_filesystem->get_contents($css_file_path) : '';
            if ($existing_content !== $minified_css) {
                $wp_filesystem->put_contents($css_file_path, $minified_css, FS_CHMOD_FILE);
            }

            // Check if file exists and is readable before enqueuing
            if (file_exists($css_file_path) && is_readable($css_file_path)) {
                $version = filemtime($css_file_path);
            } else {
                $version = time(); // Fallback version number
            }

            // Enqueue the combined CSS file
            wp_enqueue_style(
                'gutslider-combined-styles',
                $css_file_url,
                [],
                $version
            );
        }

        /**
         * Minify CSS
         */
        private function minify_css($css) {
            // Remove comments
            $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
            // Remove space after colons
            $css = str_replace(': ', ':', $css);
            // Remove whitespace
            $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
            return $css;
        }
    }
}

new GutSlider_Dynamic_Style(); // Initialize the class