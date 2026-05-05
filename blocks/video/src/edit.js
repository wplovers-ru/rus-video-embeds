import { __ } from '@wordpress/i18n';
import {
    useBlockProps,
    InspectorControls,
} from '@wordpress/block-editor';
import {
    PanelBody,
    SelectControl,
    ToggleControl,
    TextControl,
    Placeholder,
    Spinner,
    Notice,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useState, useEffect, useRef } from '@wordpress/element';

const ASPECT_RATIOS = [
    { label: '16:9', value: '16:9' },
    { label: '4:3', value: '4:3' },
    { label: '1:1', value: '1:1' },
];

const URL_PATTERNS = [
    /https?:\/\/(?:www\.)?vk\.com\/(?:video|clip)-?\d+_\d+/i,
    /https?:\/\/vkvideo\.ru\/video-?\d+_\d+/i,
    /https?:\/\/(?:www\.)?rutube\.ru\/(?:video|play\/embed)\/[a-f0-9]+\/?/i,
    /https?:\/\/(?:(?:www\.)?dzen\.ru|zen\.yandex\.ru)\/video\/watch\/[a-zA-Z0-9]+/i,
    /https?:\/\/(?:www\.)?dzen\.ru\/embed\/[a-zA-Z0-9]+/i,
];

const DZEN_WATCH_PATTERN =
    /https?:\/\/(?:(?:www\.)?dzen\.ru|zen\.yandex\.ru)\/video\/watch\/[a-zA-Z0-9]+/i;

const DZEN_IFRAME_PATTERN =
    /<iframe[^>]+src=["']([^"']*dzen\.ru\/embed\/[a-zA-Z0-9]+)[^"']*["']/i;

const DZEN_EMBED_PATTERN =
    /https?:\/\/(?:www\.)?dzen\.ru\/embed\/[a-zA-Z0-9]+/i;

/**
 * Checks if a URL matches any of the supported video providers.
 *
 * @param {string} url The URL to validate.
 * @returns {boolean} True if the URL belongs to a supported provider.
 */
function isValidProviderUrl(url) {
    return URL_PATTERNS.some((pattern) => pattern.test(url));
}

/**
 * Checks if a URL is a Dzen watch-URL (not directly embeddable).
 *
 * @param {string} url The URL to check.
 * @returns {boolean} True if the URL is a Dzen watch-URL.
 */
function isDzenWatchUrl(url) {
    return DZEN_WATCH_PATTERN.test(url);
}

/**
 * Attempts to extract a Dzen embed URL from pasted iframe HTML code.
 * Strips query parameters from the extracted src to get a clean embed URL.
 *
 * @param {string} input Raw input that may contain an iframe tag.
 * @returns {string|null} The clean embed URL, or null if not found.
 */
function extractDzenEmbedFromIframe(input) {
    if (!input.includes('<iframe')) {
        return null;
    }

    const match = input.match(DZEN_IFRAME_PATTERN);
    if (!match) {
        return null;
    }

    const srcWithParams = match[1];
    const embedMatch = srcWithParams.match(DZEN_EMBED_PATTERN);
    return embedMatch ? embedMatch[0] : null;
}

/**
 * Returns the UTM link for the Dzen embed notice.
 * Uses server-provided data from wp_localize_script, falls back gracefully.
 *
 * @returns {string} The notice URL with UTM parameters.
 */
function getDzenNoticeUrl() {
    if (typeof window.wplrveBlockData !== 'undefined' && window.wplrveBlockData.dzenNoticeUrl) {
        return window.wplrveBlockData.dzenNoticeUrl;
    }
    return 'https://wplovers.ru/dzen-wordpress/';
}

/**
 * Block editor component for the rus-video-embeds/video block.
 *
 * Renders a URL input placeholder when no URL is set, or a server-side
 * rendered preview when a valid URL is present. Provides InspectorControls
 * for aspect ratio and autoplay settings.
 *
 * Handles special Dzen cases:
 * - Watch-URL input shows a warning Notice with instructions.
 * - Pasted <iframe> code is parsed to extract embed URL.
 *
 * @param {Object}   props               Block props.
 * @param {Object}   props.attributes    Block attributes (url, aspectRatio, autoplay).
 * @param {Function} props.setAttributes Callback to update block attributes.
 * @returns {JSX.Element} The editor UI.
 */
export default function Edit({ attributes, setAttributes }) {
    const { url, aspectRatio, autoplay } = attributes;
    const blockProps = useBlockProps();
    const [inputUrl, setInputUrl] = useState(url);
    const [error, setError] = useState('');
    const [dzenWatchWarning, setDzenWatchWarning] = useState(false);
    const didInitMargin = useRef(false);

    useEffect(() => {
        if (didInitMargin.current) {
            return;
        }
        didInitMargin.current = true;

        if (url) {
            return;
        }

        const margin =
            typeof window.wplrveBlockData !== 'undefined'
                ? window.wplrveBlockData.defaultVerticalMargin
                : '';

        if (!margin) {
            return;
        }

        const presetValue = `var:preset|spacing|${margin}`;
        setAttributes({
            style: {
                ...(attributes.style || {}),
                spacing: {
                    ...(attributes.style?.spacing || {}),
                    margin: {
                        top: presetValue,
                        bottom: presetValue,
                    },
                },
            },
        });
    }, []); // eslint-disable-line react-hooks/exhaustive-deps

    /**
     * Processes raw input: detects iframe paste, Dzen watch-URLs, or regular URLs.
     *
     * @param {string} rawInput The raw text from the input field.
     */
    const processInput = (rawInput) => {
        setInputUrl(rawInput);
        setDzenWatchWarning(false);
        setError('');

        const iframeEmbed = extractDzenEmbedFromIframe(rawInput);
        if (iframeEmbed) {
            setInputUrl(iframeEmbed);
            setAttributes({ url: iframeEmbed });
            return;
        }

        if (isDzenWatchUrl(rawInput)) {
            setDzenWatchWarning(true);
            return;
        }
    };

    const handleSubmit = () => {
        if (!inputUrl.trim()) {
            setError(__('Enter a video URL', 'rus-video-embeds'));
            return;
        }

        if (dzenWatchWarning) {
            return;
        }

        if (!isValidProviderUrl(inputUrl)) {
            setError(
                __(
                    'Unrecognized URL. Supported: VK Video, Rutube, Dzen',
                    'rus-video-embeds'
                )
            );
            return;
        }

        setError('');
        setAttributes({ url: inputUrl });
    };

    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody
                    title={__('Video Settings', 'rus-video-embeds')}
                >
                    <SelectControl
                        label={__('Aspect Ratio', 'rus-video-embeds')}
                        value={aspectRatio}
                        options={ASPECT_RATIOS}
                        onChange={(value) =>
                            setAttributes({ aspectRatio: value })
                        }
                    />
                    <ToggleControl
                        label={__('Autoplay', 'rus-video-embeds')}
                        checked={autoplay}
                        onChange={(value) =>
                            setAttributes({ autoplay: value })
                        }
                    />
                </PanelBody>
            </InspectorControls>

            {!url ? (
                <Placeholder
                    icon="video-alt3"
                    label={__('RU Video', 'rus-video-embeds')}
                    instructions={__(
                        'Paste a video link from VK, Rutube, or Dzen',
                        'rus-video-embeds'
                    )}
                >
                    <TextControl
                        placeholder="https://..."
                        value={inputUrl}
                        onChange={processInput}
                        onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                handleSubmit();
                            }
                        }}
                    />
                    {dzenWatchWarning && (
                        <Notice status="warning" isDismissible={false}>
                            <p>
                                {__(
                                    'Dzen uses separate links for embedding. Click "Share" → "Embed" under the video and copy the link from the iframe code.',
                                    'rus-video-embeds'
                                )}
                            </p>
                            <p>
                                <a
                                    href={getDzenNoticeUrl()}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {__('Learn more', 'rus-video-embeds')} →
                                </a>
                            </p>
                        </Notice>
                    )}
                    {error && (
                        <p style={{ color: '#cc1818', marginTop: '8px' }}>
                            {error}
                        </p>
                    )}
                    <button
                        type="button"
                        className="components-button is-primary"
                        onClick={handleSubmit}
                    >
                        {__('Embed', 'rus-video-embeds')}
                    </button>
                </Placeholder>
            ) : (
                <>
                    <ServerSideRender
                        block="rus-video-embeds/video"
                        attributes={attributes}
                        LoadingResponsePlaceholder={() => (
                            <Placeholder
                                icon="video-alt3"
                                label={__('Loading…', 'rus-video-embeds')}
                            >
                                <Spinner />
                            </Placeholder>
                        )}
                        ErrorResponsePlaceholder={() => (
                            <Placeholder
                                icon="warning"
                                label={__(
                                    'Ошибка загрузки превью',
                                    'rus-video-embeds'
                                )}
                            />
                        )}
                    />
                    <TextControl
                        value={url}
                        onChange={(value) => {
                            const iframeEmbed = extractDzenEmbedFromIframe(value);
                            if (iframeEmbed) {
                                setInputUrl(iframeEmbed);
                                setAttributes({ url: iframeEmbed });
                                setError('');
                                setDzenWatchWarning(false);
                                return;
                            }

                            if (isDzenWatchUrl(value)) {
                                setDzenWatchWarning(true);
                                setError('');
                                return;
                            }

                            setDzenWatchWarning(false);
                            setInputUrl(value);
                            if (isValidProviderUrl(value)) {
                                setAttributes({ url: value });
                                setError('');
                            } else if (value) {
                                setError(
                                    __(
                                        'Unrecognized URL. Supported: VK Video, Rutube, Dzen',
                                        'rus-video-embeds'
                                    )
                                );
                            }
                        }}
                        help={error || undefined}
                    />
                    {dzenWatchWarning && (
                        <Notice status="warning" isDismissible={false}>
                            <p>
                                {__(
                                    'Dzen uses separate links for embedding. Click "Share" → "Embed" under the video and copy the link from the iframe code.',
                                    'rus-video-embeds'
                                )}
                            </p>
                            <p>
                                <a
                                    href={getDzenNoticeUrl()}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {__('Learn more', 'rus-video-embeds')} →
                                </a>
                            </p>
                        </Notice>
                    )}
                </>
            )}
        </div>
    );
}
