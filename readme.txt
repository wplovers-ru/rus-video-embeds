=== RUS Video Embeds ===
Contributors: rusvideoembeds
Tags: video, embed, vk, rutube, dzen, russian, oembed, gutenberg
Requires at least: 5.8
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Автоматическая вставка видео с VK Видео, Rutube и Дзен — oEmbed, шорткоды и Gutenberg-блок.

== Description ==

RUS Video Embeds добавляет поддержку российских видеохостингов в WordPress:

* **VK Видео** — vk.com/video*, vkvideo.ru/*
* **Rutube** — rutube.ru/video/*
* **Дзен** — dzen.ru/video/watch/*, zen.yandex.ru/*

**Возможности:**

* Автоматическая вставка видео по URL (oEmbed) — просто вставьте ссылку на отдельной строке
* Шорткоды `[vk_video]`, `[rutube]`, `[dzen]` для классического редактора
* Gutenberg-блок «Видео RU» с превью и настройками
* Адаптивный responsive iframe (16:9 по умолчанию)
* Страница настроек: размеры по умолчанию, автоплей, включение/отключение провайдеров
* Безопасность: sandbox iframe, lazy loading, валидация URL
* Расширяемость: добавляйте свои провайдеры через фильтр `rve_register_providers`

== Installation ==

1. Загрузите папку `rus-video-embeds` в `/wp-content/plugins/`
2. Активируйте плагин через меню «Плагины» в WordPress
3. Настройте плагин в «Настройки» → «Видео RU Embed»

== Usage ==

**oEmbed (автоматически):**
Просто вставьте ссылку на видео на отдельной строке в редакторе:
`https://rutube.ru/video/abc123def456/`

**Шорткоды:**
`[vk_video url="https://vk.com/video-123456_789012"]`
`[rutube url="https://rutube.ru/video/abc123/" width="800" height="450"]`
`[dzen url="https://dzen.ru/video/watch/abc123" autoplay="1"]`

**Gutenberg:**
Добавьте блок «Видео RU» и вставьте URL.

== Frequently Asked Questions ==

= Какие видеохостинги поддерживаются? =

VK Видео, Rutube и Дзен. Можно добавить свои через фильтр `rve_register_providers`.

= Приватные видео работают? =

Embed работает только для публичных видео. Приватные видео VK могут не отображаться.

== Changelog ==

= 1.0.0 =
* Первый релиз
* Поддержка VK Видео, Rutube, Дзен
* oEmbed, шорткоды, Gutenberg-блок
* Страница настроек
* Адаптивный responsive iframe
