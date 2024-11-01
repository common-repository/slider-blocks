<?php
/**
 * Register Blocks 
 * @package GutSliderBlocks
 */

declare(strict_types=1);

namespace GutSlider\Blocks;

defined('ABSPATH') || exit;

final class BlockRegistration {
    /**
     * Block configuration
     */
    private const BLOCKS = [
        'content-slider'     => 'gut_fixed_content_slider',
        'any-content'        => 'gut_any_content_slider',
        'testimonial-slider' => 'gut_testimonial_slider',
        'photo-carousel'     => 'gut_photo_carousel',
        'logo-carousel'      => 'gut_logo_carousel',
        'before-after'       => 'gut_before_after_slider',
        'videos-carousel'    => 'gut_videos_carousel',
        'post-slider'        => 'gut_post_slider'
    ];

    /**
     * Initialize the block registration
     */
    public static function init(): void {
        add_action('init', [new self(), 'register_blocks']);
    }

    /**
     * Register active blocks
     */
    public function register_blocks(): void {
        $active_blocks = $this->get_active_blocks();
        
        foreach ($active_blocks as $block) {
            $this->register_single_block($block);
        }
    }

    /**
     * Get list of active blocks
     * @return array
     */
    private function get_active_blocks(): array {
        return array_filter(
            array_keys(self::BLOCKS),
            [$this, 'is_block_enabled']
        );
    }

    /**
     * Check if a block is enabled
     * @param string $block_name
     * @return bool
     */
    private function is_block_enabled(string $block_name): bool {
        $option_name = self::BLOCKS[$block_name] ?? '';
        return !empty($option_name) && get_option($option_name, true);
    }

    /**
     * Register a single block
     * @param string $block_name
     */
    private function register_single_block(string $block_name): void {
        $block_path = $this->get_block_path($block_name);
        
        if (file_exists($block_path)) {
            register_block_type($block_path);
        }
    }

    /**
     * Get the full path for a block
     * @param string $block_name
     * @return string
     */
    private function get_block_path(string $block_name): string {
        return GUTSLIDER_DIR . "/build/blocks/{$block_name}";
    }
}

// Initialize the class
add_action('plugins_loaded', [BlockRegistration::class, 'init']);