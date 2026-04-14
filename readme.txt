=== RUS Video Embeds - insert VK video, Rutube, Dzen ===
Contributors: wplovers, donatory
Tags: video, embed, vkvideo, rutube, dzen
Requires at least: 5.8
Tested up to: 6.8
Stable tag: 1.1.0
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

**⚠️ Dzen notice:** Regular video links (`dzen.ru/video/watch/...`) **do not work** for embedding — Dzen uses separate embed links. When a watch-link is pasted, the plugin displays instructions on how to get the correct link. More info: [How to embed Dzen video in WordPress](https://wplovers.ru/dzen-wordpress/?utm_source=wordpress.org&utm_content=dzen_embed)

**Features:**

* Auto-embed videos by URL (oEmbed) — just paste a link on its own line
* Shortcodes `[vk_video]`, `[rutube]`, `[dzen]` for the Classic Editor
* Gutenberg block "RU Video" with preview and settings
* Responsive iframe (16:9 by default)
* Configurable vertical margins via Gutenberg spacing presets
* Settings page: default dimensions, autoplay, margins, enable/disable providers
* Security: sandboxed iframe, lazy loading, URL validation
* Extensible: add your own providers via the `rve_register_providers` filter

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

== Frequently Asked Questions ==

= Which video platforms are supported? =

VK Video, Rutube, and Dzen. You can add your own via the `rve_register_providers` filter.

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
* Settings page
* Responsive iframe

== Upgrade Notice ==

= 1.1.0 =
Adds Dzen embed-URL support, helpful notices for watch-URLs, configurable vertical margins, and editor fixes.

= 1.0.0 =
Initial release.
