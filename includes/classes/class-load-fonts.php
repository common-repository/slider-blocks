<?php
/**
 * Load Google Fonts
 * @package GutSliderBlocks
 */

declare(strict_types=1);

namespace GutSlider\Fonts;

defined('ABSPATH') || exit;

final class FontLoader {
    /**
     * System fonts that don't need to be loaded from Google
     */
    private const SYSTEM_FONTS = [
        'Default',
        'Arial',
        'Tahoma',
        'Verdana',
        'Helvetica',
        'Times New Roman',
        'Trebuchet MS',
        'Georgia',
    ];

    /**
     * Google Fonts attribute string
     */
    private const FONT_WEIGHTS = ':100,200,300,400,500,600,700,800,900';

    /**
     * Store all fonts to be loaded
     * @var array
     */
    private static array $fonts = [];

    /**
     * Initialize the font loader
     */
    public static function init(): void {
        $instance = new self();
        $instance->setup_hooks();
    }

    /**
     * Setup hooks
     */
    private function setup_hooks(): void {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_fonts'], 10);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_fonts'], 10);
        add_action('gutsliders_render_block', [$this, 'collect_fonts']);
    }

    /**
     * Collect fonts from block attributes
     * @param array $block
     */
    public function collect_fonts(array $block): void {
        if (empty($block['attrs']) || !is_array($block['attrs'])) {
            return;
        }

        foreach ($block['attrs'] as $key => $value) {
            if ($this->is_font_family_attribute($key, $value)) {
                self::$fonts[] = $value;
            }
        }
    }

    /**
     * Check if attribute is a font family
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    private function is_font_family_attribute(string $key, $value): bool {
        return !empty($value) && 
               strpos($key, 'gutsliders_') === 0 && 
               strpos($key, 'FontFamily') !== false;
    }

    /**
     * Enqueue Google Fonts
     */
    public function enqueue_fonts(): void {
        $google_fonts = $this->get_google_fonts();
        
        foreach ($google_fonts as $font) {
            $this->enqueue_google_font($font);
        }
    }

    /**
     * Get filtered Google Fonts
     * @return array
     */
    private function get_google_fonts(): array {
        $unique_fonts = array_unique(self::$fonts);
        return array_filter($unique_fonts, function($font) {
            return !empty($font) && !in_array($font, self::SYSTEM_FONTS, true);
        });
    }

    /**
     * Enqueue individual Google Font
     * @param string $font
     */
    private function enqueue_google_font(string $font): void {
        $font_family = str_replace(' ', '+', trim($font)) . self::FONT_WEIGHTS;
        $font_handle = 'gutsliders-font-' . sanitize_title($font);

        wp_enqueue_style(
            $font_handle,
            esc_url_raw("https://fonts.googleapis.com/css?family={$font_family}&display=swap"),
            [],
            GUTSLIDER_VERSION
        );
    }
}

// Initialize the class
add_action('init', [FontLoader::class, 'init']);