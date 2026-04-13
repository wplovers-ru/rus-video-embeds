<?php

declare(strict_types=1);

namespace RusVideoEmbeds\Admin;

use RusVideoEmbeds\Providers\ProviderRegistry;

/**
 * Admin settings page for the RUS Video Embeds plugin.
 *
 * Registers a settings page under Settings → Видео RU Embed
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
            __('Видео RU Embed — Настройки', 'rus-video-embeds'),
            __('Видео RU Embed', 'rus-video-embeds'),
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
            __('Основные настройки', 'rus-video-embeds'),
            '__return_false',
            self::PAGE_SLUG
        );

        add_settings_field(
            'default_width',
            __('Ширина по умолчанию (px)', 'rus-video-embeds'),
            [self::class, 'renderNumberField'],
            self::PAGE_SLUG,
            'rve_general',
            ['field' => 'default_width']
        );

        add_settings_field(
            'default_height',
            __('Высота по умолчанию (px)', 'rus-video-embeds'),
            [self::class, 'renderNumberField'],
            self::PAGE_SLUG,
            'rve_general',
            ['field' => 'default_height']
        );

        add_settings_field(
            'default_autoplay',
            __('Автоплей по умолчанию', 'rus-video-embeds'),
            [self::class, 'renderCheckboxField'],
            self::PAGE_SLUG,
            'rve_general',
            ['field' => 'default_autoplay']
        );

        add_settings_field(
            'enabled_providers',
            __('Включённые провайдеры', 'rus-video-embeds'),
            [self::class, 'renderProvidersField'],
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
            'default_width'      => 800,
            'default_height'     => 450,
            'default_autoplay'   => false,
            'enabled_providers'  => ['vk_video', 'rutube', 'dzen'],
        ];
    }
}
