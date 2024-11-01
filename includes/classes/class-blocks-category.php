<?php 
/**
 * Register Blocks Category
 * @package GutSliderBlocks
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

if ( ! class_exists( 'GutSlider_Blocks_Category' ) ) {

    class GutSlider_Blocks_Category {

        /**
         * Constructor
         */
        public function __construct() {
            $filter = version_compare( $GLOBALS['wp_version'], '5.7', '<' ) ? 'block_categories' : 'block_categories_all';
            add_filter( $filter, [ $this, 'register_block_category' ], 10, 2 );
        }

        /**
         * Register Block Category
         * @param array $categories
         * @param WP_Post $post
         * @return array
         */
        public function register_block_category( $categories, $post ) {
            $new_category = array(
                'slug'  => 'slider-blocks',
                'title' => __( 'GutSlider Blocks', 'slider-blocks' ),
            );
            return array_merge( array( $new_category ), $categories );
        }

    }

    new GutSlider_Blocks_Category(); // Initialize the class
}
