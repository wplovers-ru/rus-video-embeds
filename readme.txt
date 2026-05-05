=== RUS Video Embeds for VK Video, Rutube and Dzen ===
Contributors: wplovers, donatory
Tags: video, embed, vkvideo, rutube, dzen
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 1.1.3
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://wplovers.ru/donate/
Embed videos from VK Video, Rutube, and Dzen — oEmbed, shortcodes, and a Gutenberg block.

== Description ==

RUS Video Embeds adds support for Russian video hosting platforms in WordPress:

* **VK Video** — vk.com/video*, vkvideo.ru/*
* **Rutube** — rutube.ru/video/*
* **Dzen** — dzen.ru/embed/* (embed links)

This is an unofficial plugin and is not affiliated with VK, Rutube, or Dzen.

**⚠️ Dzen notice:** Regular video links (`dzen.ru/video/watch/...`) **do not work** for embedding — Dzen uses separate embed links. When a watch-link is pasted, the plugin displays instructions on how to get the correct link. More info: [How to embed Dzen video in WordPress](https://wplovers.ru/dzen-wordpress/?utm_source=wordpress.org&utm_content=dzen_embed)

**Features:**

* Auto-embed videos by URL (oEmbed) — just paste a link on its own line
* Shortcodes `[vk_video]`, `[rutube]`, `[dzen]` for the Classic Editor
* Gutenberg block "RU Video" with preview and settings
* Responsive iframe (16:9 by default)
* Configurable vertical margins via Gutenberg spacing presets
* Settings page: default dimensions, autoplay, margins, enable/disable providers
* Security: sandboxed iframe, lazy loading, URL validation
* Extensible: add your own providers via the `rus_video_embeds_register_providers` filter

== Installation ==

1. Upload the `rus-video-embeds` folder to `/wp-content/plugins/`
2. Activate the plugin through the "Plugins" menu in WordPress
3. Configure the plugin under "Settings" → "RUS Video Embeds"

== Usage ==

**oEmbed (automatic):**
Simply paste a video link on its own line in the editor:
`https://rutube.ru/video/abc123def456/`

**Shortcodes:**
`[vk_video url="https://vk.com/video-123456_789012"]`
`[rutube url="https://rutube.ru/video/abc123/" width="800" height="450"]`
`[dzen url="https://dzen.ru/embed/abc123def456" autoplay="1"]`

**Gutenberg:**
Add the "RU Video" block and paste the URL.

**Dzen — how to get an embed link:**

1. Open the video on Dzen
2. Click "Share" → "Embed"
3. Copy the link from the `src` attribute in the iframe code (format: `https://dzen.ru/embed/...`)
4. Paste this link into the block, shortcode, or oEmbed

You can also paste the entire `<iframe>` code into the Gutenberg block — the plugin will automatically extract the embed URL.

Detailed instructions with screenshots: [How to embed Dzen video in WordPress](https://wplovers.ru/dzen-wordpress/?utm_source=wordpress.org&utm_content=dzen_embed)

== External services ==

This plugin uses external services to resolve and display video preview images in the FluentCommunity integration.

1) **VK video embed page (`vk.com`, `vkvideo.ru`)**
- **What the service is used for:** The plugin requests the video embed page to extract preview image metadata for FluentCommunity cards.
- **What data is sent and when:** When a VK preview is generated or refreshed, WordPress sends an outbound HTTP GET request to the video embed URL. The request includes the target video URL and a standard WordPress user agent string (`WP-URLDetails/... (+site-url)`).
- **Provider links:** Terms of Service: https://vk.com/terms ; Privacy Policy: https://vk.com/privacy

2) **VK preview image CDN (`iv.okcdn.ru`)**
- **What the service is used for:** The plugin builds and uses the final VK preview image URL from `iv.okcdn.ru` to show the thumbnail in FluentCommunity previews.
- **What data is sent and when:** When preview metadata is parsed for VK embeds, the plugin constructs an external image URL on `iv.okcdn.ru`. The browser and/or WordPress may request this image URL when rendering preview cards.
- **Provider links:** Terms of Service: https://ok.ru/regulations ; Privacy Policy: https://ok.ru/privacy

3) **Rutube preview image CDN (`rtbcdn.ru`)**
- **What the service is used for:** Rutube thumbnails used in preview cards are loaded from Rutube CDN domains such as `rtbcdn.ru`.
- **What data is sent and when:** When a Rutube URL is parsed by FluentCommunity (`RemoteUrlParser`) and the preview is rendered, the thumbnail URL returned by Rutube metadata is requested by the browser/WordPress.
- **Provider links:** Terms of Service: https://rutube.ru/info/agreement/ ; Privacy Policy: https://rutube.ru/info/privacy/

4) **Dzen preview image CDN (`avatars.dzeninfra.ru`)**
- **What the service is used for:** Dzen thumbnails used in preview cards are loaded from Dzen infrastructure domains such as `avatars.dzeninfra.ru`.
- **What data is sent and when:** When a Dzen URL is parsed by FluentCommunity (`RemoteUrlParser`) and the preview is rendered, the thumbnail URL returned by Dzen metadata is requested by the browser/WordPress.
- **Provider links:** Terms of Service: https://dzen.ru/legal/ru/termsofuse/index.html ; Privacy Policy: https://yandex.ru/legal/confidential/

== Development / Build ==

JavaScript source code for block assets is included in this plugin package:
`blocks/video/src/`

Compiled production assets used at runtime are located in:
`blocks/video/build/`

Build commands:
1. `npm install`
2. `npm run build`

Public source repositories:
- WordPress.org plugin SVN: https://plugins.trac.wordpress.org/browser/rus-video-embeds/
- GitHub mirror: https://github.com/wplovers-ru/rus-video-embeds

== Frequently Asked Questions ==

= Which video platforms are supported? =

VK Video, Rutube, and Dzen. You can add your own via the `rus_video_embeds_register_providers` filter.

= Do private videos work? =

Embedding only works for public videos. Private VK videos may not display.

= Why doesn't my Dzen video link work? =

Dzen uses different links for viewing and embedding. A regular link like `dzen.ru/video/watch/...` won't work for embeds. You need a special embed link in the format `dzen.ru/embed/...`. To get it, click "Share" → "Embed" under the video and copy the link from the iframe code. [Detailed instructions](https://wplovers.ru/dzen-wordpress/?utm_source=wordpress.org&utm_content=dzen_embed)

== Screenshots ==

1. Gutenberg block — paste a video URL and get an instant preview
2. Settings page — default dimensions, autoplay, margins, providers
3. Dzen embed notice — helpful instructions when a watch-URL is pasted
4. Frontend — responsive video embed on the site

== Changelog ==

= 1.1.3 =
* Renamed plugin title to "RUS Video Embeds for VK Video, Rutube and Dzen" for WordPress.org moderation compliance
* Replaced inline embed JS with enqueued script and fixed Gutenberg preview scrollbar behavior

= 1.1.2 =
* FluentCommunity: stable refresh of VK thumbnails when editing older posts
* FluentCommunity: media_preview now refreshes when image is missing or stale

= 1.1.1 =
* Fixed global constant prefixes for WordPress.org Plugin Check compliance
* Added core `pre_oembed_result` integration for better compatibility with third-party oEmbed pipelines (e.g., FluentCommunity)
* Added ABSPATH guards, LICENSE, uninstall.php
* Translated all strings to English for WordPress.org; Russian available via translate.wordpress.org

= 1.1.0 =
* Dzen: informative notice with instructions when a watch-URL is pasted instead of a broken iframe
* Dzen: full embed-URL support (`dzen.ru/embed/*`) across all contexts
* Dzen: iframe code parsing in Gutenberg block — automatic embed-URL extraction
* Fixed scrollbars in Gutenberg and Classic Editor — inline styles for self-contained rendering
* Added editor CSS for correct preview rendering in editors
* Default vertical margin setting (Gutenberg spacing presets) in plugin settings
* `spacing.margin` support in Gutenberg block with auto-applied default value
* Updated plugin name and settings menu
* Updated readme.txt with Dzen instructions and full changelog

= 1.0.0 =
* Initial release
* VK Video, Rutube, Dzen support
* oEmbed, shortcodes, Gutenberg block
= 1.1.1 =
* Settings page
* Responsive iframe

== Upgrade Notice ==

= 1.1.3 =
Renamed plugin title for moderation compliance and moved/fixed embed JS handling via WordPress enqueue pipeline.

= 1.1.2 =
Improves VK thumbnail refresh stability for older FluentCommunity posts during edits.


= 1.1.1 =
Fixed constant prefixes for Plugin Check compliance, translated to English, added required WordPress.org files.

= 1.1.0 =
Adds Dzen embed-URL support, helpful notices for watch-URLs, configurable vertical margins, and editor fixes.

= 1.0.0 =
Initial release.
