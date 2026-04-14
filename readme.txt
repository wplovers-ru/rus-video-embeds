=== RUS Video Embeds - insert VK video, Rutube, Dzen ===
Contributors: wplovers, donatory
Tags: video, embed, vk, rutube, dzen, russian, oembed, gutenberg
Requires at least: 5.8
Tested up to: 6.8
Stable tag: 1.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Автоматическая вставка видео с VK Видео, Rutube и Дзен — oEmbed, шорткоды и Gutenberg-блок.

== Description ==

RUS Video Embeds добавляет поддержку российских видеохостингов в WordPress:

* **VK Видео** — vk.com/video*, vkvideo.ru/*
* **Rutube** — rutube.ru/video/*
* **Дзен** — dzen.ru/embed/* (embed-ссылки)

**⚠️ Особенность Дзен:** обычные ссылки на видео (`dzen.ru/video/watch/...`) **не работают** для встраивания — Дзен использует отдельные embed-ссылки. При вставке watch-ссылки плагин покажет инструкцию, как получить правильную ссылку. Подробнее: [Как вставить видео с Дзен в WordPress](https://wplovers.ru/dzen-wordpress/?utm_source=wordpress.org&utm_content=dzen_embed)

**Возможности:**

* Автоматическая вставка видео по URL (oEmbed) — просто вставьте ссылку на отдельной строке
* Шорткоды `[vk_video]`, `[rutube]`, `[dzen]` для классического редактора
* Gutenberg-блок «Видео RU» с превью и настройками
* Адаптивный responsive iframe (16:9 по умолчанию)
* Настраиваемые вертикальные отступы (margin) через Gutenberg spacing presets
* Страница настроек: размеры по умолчанию, автоплей, отступы, включение/отключение провайдеров
* Безопасность: sandbox iframe, lazy loading, валидация URL
* Расширяемость: добавляйте свои провайдеры через фильтр `rve_register_providers`

== Installation ==

1. Загрузите папку `rus-video-embeds` в `/wp-content/plugins/`
2. Активируйте плагин через меню «Плагины» в WordPress
3. Настройте плагин в «Настройки» → «RUS Video Embeds»

== Usage ==

**oEmbed (автоматически):**
Просто вставьте ссылку на видео на отдельной строке в редакторе:
`https://rutube.ru/video/abc123def456/`

**Шорткоды:**
`[vk_video url="https://vk.com/video-123456_789012"]`
`[rutube url="https://rutube.ru/video/abc123/" width="800" height="450"]`
`[dzen url="https://dzen.ru/embed/abc123def456" autoplay="1"]`

**Gutenberg:**
Добавьте блок «Видео RU» и вставьте URL.

**Дзен — как получить embed-ссылку:**

1. Откройте видео на Дзен
2. Нажмите «Поделиться» → «Встроить»
3. Скопируйте ссылку из `src` в iframe-коде (формат: `https://dzen.ru/embed/...`)
4. Вставьте эту ссылку в блок, шорткод или oEmbed

Также в Gutenberg-блоке можно вставить весь код `<iframe>` — плагин автоматически извлечёт embed-URL.

Подробная инструкция со скриншотами: [Как вставить видео с Дзен в WordPress](https://wplovers.ru/dzen-wordpress/?utm_source=wordpress.org&utm_content=dzen_embed)

== Frequently Asked Questions ==

= Какие видеохостинги поддерживаются? =

VK Видео, Rutube и Дзен. Можно добавить свои через фильтр `rve_register_providers`.

= Приватные видео работают? =

Embed работает только для публичных видео. Приватные видео VK могут не отображаться.

= Почему не работает ссылка на видео Дзен? =

Дзен использует разные ссылки для просмотра и встраивания. Обычная ссылка `dzen.ru/video/watch/...` не работает для embed. Нужна специальная embed-ссылка формата `dzen.ru/embed/...`. Чтобы её получить, нажмите «Поделиться» → «Встроить» под видео и скопируйте ссылку из iframe-кода. [Подробная инструкция](https://wplovers.ru/dzen-wordpress/?utm_source=wordpress.org&utm_content=dzen_embed)

== Changelog ==

= 1.1.0 =
* Дзен: информативная заглушка при вставке watch-URL с инструкцией вместо нерабочего iframe
* Дзен: полная поддержка embed-URL (`dzen.ru/embed/*`) во всех контекстах
* Дзен: парсинг вставленного `<iframe>` кода в Gutenberg-блоке — автоматическое извлечение embed-URL
* Исправлены скроллбары в Gutenberg и Classic Editor — inline-стили для self-contained рендеринга
* Добавлен editor CSS для корректного отображения превью в редакторах
* Настройка вертикальных отступов по умолчанию (Gutenberg spacing presets) в настройках плагина
* Поддержка `spacing.margin` в Gutenberg-блоке с автоприменением дефолтного значения
* Финальное исправление скроллбаров: inline-стили в EmbedRenderer, editorStyle в block.json
* Обновлено название плагина и меню настроек
* Обновлён readme.txt с инструкциями по Дзен и полным changelog

= 1.0.0 =
* Первый релиз
* Поддержка VK Видео, Rutube, Дзен
* oEmbed, шорткоды, Gutenberg-блок
* Страница настроек
* Адаптивный responsive iframe
