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
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useState } from '@wordpress/element';

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
];

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
 * Block editor component for the rus-video-embeds/video block.
 *
 * Renders a URL input placeholder when no URL is set, or a server-side
 * rendered preview when a valid URL is present. Provides InspectorControls
 * for aspect ratio and autoplay settings.
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

    const handleSubmit = () => {
        if (!inputUrl.trim()) {
            setError(__('Введите URL видео', 'rus-video-embeds'));
            return;
        }

        if (!isValidProviderUrl(inputUrl)) {
            setError(
                __(
                    'URL не распознан. Поддерживаются: VK Видео, Rutube, Дзен',
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
                    title={__('Настройки видео', 'rus-video-embeds')}
                >
                    <SelectControl
                        label={__('Соотношение сторон', 'rus-video-embeds')}
                        value={aspectRatio}
                        options={ASPECT_RATIOS}
                        onChange={(value) =>
                            setAttributes({ aspectRatio: value })
                        }
                    />
                    <ToggleControl
                        label={__('Автоплей', 'rus-video-embeds')}
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
                    label={__('Видео RU', 'rus-video-embeds')}
                    instructions={__(
                        'Вставьте ссылку на видео с VK, Rutube или Дзен',
                        'rus-video-embeds'
                    )}
                >
                    <TextControl
                        placeholder="https://..."
                        value={inputUrl}
                        onChange={setInputUrl}
                        onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                handleSubmit();
                            }
                        }}
                    />
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
                        {__('Встроить', 'rus-video-embeds')}
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
                                label={__('Загрузка…', 'rus-video-embeds')}
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
                            setInputUrl(value);
                            if (isValidProviderUrl(value)) {
                                setAttributes({ url: value });
                                setError('');
                            } else if (value) {
                                setError(
                                    __(
                                        'URL не распознан. Поддерживаются: VK Видео, Rutube, Дзен',
                                        'rus-video-embeds'
                                    )
                                );
                            }
                        }}
                        help={error || undefined}
                    />
                </>
            )}
        </div>
    );
}
