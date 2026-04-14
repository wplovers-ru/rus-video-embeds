<?php
declare(strict_types=1);

namespace RusVideoEmbeds\Admin;

defined('ABSPATH') || exit;

use RusVideoEmbeds\Providers\ProviderRegistry;

/**
 * Admin settings page for the RUS Video Embeds plugin.
 *
 * Registers a settings page under Settings → RUS Video Embeds
 * with fields for default dimensions, autoplay, and enabled providers.
 * All fields are protected via Settings API nonces and sanitize callbacks.
 */
class SettingsPage
{
    private const OPTION_GROUP = 'rve_settings_group';
    private const OPTION_NAME  = 'rve_settings';
    private const PAGE_SLUG    = 'rve-settings';

    /**
     * Registers the admin menu item and settings fields.
     *
     * @return void
     */
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addMenuPage']);
        add_action('admin_init', [self::class, 'registerSettings']);
    }

    /**
     * Adds the settings page under the Settings menu.
     *
     * @return void
     */
    public static function addMenuPage(): void
    {
        add_options_page(
            __('RUS Video Embeds — Settings', 'rus-video-embeds'),
            __('RUS Video Embeds', 'rus-video-embeds'),
            'manage_options',
            self::PAGE_SLUG,
            [self::class, 'renderPage']
        );
    }

    /**
     * Registers settings, sections, and fields via the Settings API.
     *
     * @return void
     */
    public static function registerSettings(): void
    {
        register_setting(
            self::OPTION_GROUP,
            self::OPTION_NAME,
            [
                'type'              => 'array',
                'sanitize_callback' => [self::class, 'sanitize'],
                'default'           => self::getDefaults(),
            ]
        );

        add_settings_section(
            'rve_general',
            __('General Settings', 'rus-video-embeds'),
            '__return_false',
            self::PAGE_SLUG
        );

        add_settings_field(
            'default_width',
            __('Default Width (px)', 'rus-video-embeds'),
            [self::class, 'renderNumberField'],
            self::PAGE_SLUG,
            'rve_general',
            ['field' => 'default_width']
        );

        add_settings_field(
            'default_height',
            __('Default Height (px)', 'rus-video-embeds'),
            [self::class, 'renderNumberField'],
            self::PAGE_SLUG,
            'rve_general',
            ['field' => 'default_height']
        );

        add_settings_field(
            'default_autoplay',
            __('Default Autoplay', 'rus-video-embeds'),
            [self::class, 'renderCheckboxField'],
            self::PAGE_SLUG,
            'rve_general',
            ['field' => 'default_autoplay']
        );

        add_settings_field(
            'enabled_providers',
            __('Enabled Providers', 'rus-video-embeds'),
            [self::class, 'renderProvidersField'],
            self::PAGE_SLUG,
            'rve_general'
        );

        add_settings_field(
            'default_vertical_margin',
            __('Default Vertical Margin', 'rus-video-embeds'),
            [self::class, 'renderMarginField'],
            self::PAGE_SLUG,
            'rve_general'
        );
    }

    /**
     * Renders the settings page HTML.
     *
     * @return void
     */
    public static function renderPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields(self::OPTION_GROUP);
                do_settings_sections(self::PAGE_SLUG);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Renders a numeric input field for the settings page.
     *
     * @param array $args {
     *     @type string $field Option key name.
     * }
     * @return void
     */
    public static function renderNumberField(array $args): void
    {
        $options = get_option(self::OPTION_NAME, self::getDefaults());
        $field   = $args['field'];
        $value   = $options[$field] ?? self::getDefaults()[$field];
        printf(
            '<input type="number" name="%s[%s]" value="%d" min="0" max="3840" class="small-text">',
            esc_attr(self::OPTION_NAME),
            esc_attr($field),
            (int) $value
        );
    }

    /**
     * Renders a checkbox input for the settings page.
     *
     * @param array $args {
     *     @type string $field Option key name.
     * }
     * @return void
     */
    public static function renderCheckboxField(array $args): void
    {
        $options = get_option(self::OPTION_NAME, self::getDefaults());
        $field   = $args['field'];
        $checked = !empty($options[$field]);
        printf(
            '<input type="checkbox" name="%s[%s]" value="1" %s>',
            esc_attr(self::OPTION_NAME),
            esc_attr($field),
            checked($checked, true, false)
        );
    }

    /**
     * Renders checkboxes for each registered video provider.
     *
     * @return void
     */
    public static function renderProvidersField(): void
    {
        $options  = get_option(self::OPTION_NAME, self::getDefaults());
        $enabled  = $options['enabled_providers'] ?? array_keys(ProviderRegistry::getInstance()->getAll());
        $allProviders = ProviderRegistry::getInstance()->getAll();

        foreach ($allProviders as $slug => $provider) {
            $isChecked = in_array($slug, $enabled, true);
            printf(
                '<label style="display:block;margin-bottom:4px;">'
                . '<input type="checkbox" name="%s[enabled_providers][]" value="%s" %s> %s'
                . '</label>',
                esc_attr(self::OPTION_NAME),
                esc_attr($slug),
                checked($isChecked, true, false),
                esc_html($provider->getName())
            );
        }
    }

    /**
     * Renders a select dropdown for the default vertical margin setting.
     *
     * Options correspond to Gutenberg spacing preset slugs
     * (mapped to CSS variables --wp--preset--spacing--{slug}).
     *
     * @return void
     */
    public static function renderMarginField(): void
    {
        $options = get_option(self::OPTION_NAME, self::getDefaults());
        $value   = $options['default_vertical_margin'] ?? '';

        $presets = self::getSpacingPresets();

        printf(
            '<select name="%s[default_vertical_margin]">',
            esc_attr(self::OPTION_NAME)
        );

        foreach ($presets as $slug => $label) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($slug),
                selected($value, $slug, false),
                esc_html($label)
            );
        }

        echo '</select>';
        echo '<p class="description">'
            . esc_html__('Applies to new blocks. Existing blocks will keep their settings.', 'rus-video-embeds')
            . '</p>';
    }

    /**
     * Returns available Gutenberg spacing presets for the margin select field.
     *
     * @return array<string, string> Slug => human-readable label.
     */
    private static function getSpacingPresets(): array
    {
        return [
            ''   => __('No margin', 'rus-video-embeds'),
            '20' => '20 (XS)',
            '30' => '30 (S)',
            '40' => '40 (M)',
            '50' => '50 (L)',
            '60' => '60 (XL)',
            '70' => '70 (2XL)',
            '80' => '80 (3XL)',
        ];
    }

    /**
     * Sanitizes settings values before saving.
     *
     * Ensures numeric values are positive integers and enabled_providers
     * contains only valid provider slugs.
     *
     * @param mixed $input Raw form input.
     * @return array Sanitised settings array.
     */
    public static function sanitize($input): array
    {
        $defaults  = self::getDefaults();
        $sanitized = [];

        $sanitized['default_width'] = isset($input['default_width']) && (int) $input['default_width'] > 0
            ? (int) $input['default_width']
            : $defaults['default_width'];

        $sanitized['default_height'] = isset($input['default_height']) && (int) $input['default_height'] > 0
            ? (int) $input['default_height']
            : $defaults['default_height'];

        $sanitized['default_autoplay'] = !empty($input['default_autoplay']);

        $validSlugs = array_keys(ProviderRegistry::getInstance()->getAll());
        $sanitized['enabled_providers'] = isset($input['enabled_providers']) && is_array($input['enabled_providers'])
            ? array_values(array_intersect($input['enabled_providers'], $validSlugs))
            : $validSlugs;

        $validMargins = ['', '20', '30', '40', '50', '60', '70', '80'];
        $marginValue  = $input['default_vertical_margin'] ?? '';
        $sanitized['default_vertical_margin'] = in_array($marginValue, $validMargins, true)
            ? $marginValue
            : $defaults['default_vertical_margin'];

        return $sanitized;
    }

    /**
     * Returns the default option values.
     *
     * @return array{default_width: int, default_height: int, default_autoplay: bool, enabled_providers: string[]}
     */
    private static function getDefaults(): array
    {
        return [
            'default_width'           => 800,
            'default_height'          => 450,
            'default_autoplay'        => false,
            'enabled_providers'       => ['vk_video', 'rutube', 'dzen'],
            'default_vertical_margin' => '',
        ];
    }
}
