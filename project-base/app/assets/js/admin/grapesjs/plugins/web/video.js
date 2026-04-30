import grapesjs from 'grapesjs';

const YOUTUBE_PROVIDERS = ['yt', 'ytnc'];
const YOUTUBE_REFERRER_POLICY = 'strict-origin-when-cross-origin';
const YOUTUBE_ALLOW =
    'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
const DEPRECATED_YOUTUBE_TRAIT_NAMES = ['modestbranding'];
const EDITOR_PREVIEW_WIDTH = '800px';
const EDITOR_PREVIEW_HEIGHT = '450px';

const applyStyles = (element, styles) => {
    Object.assign(element.style, styles);

    return element;
};

const getYoutubeVideoId = videoId => videoId.split('?')[0].split('&')[0];

const createYoutubeThumbnail = () =>
    applyStyles(document.createElement('img'), {
        height: '100%',
        objectFit: 'cover',
        width: '100%',
    });

const createPlayIcon = () =>
    applyStyles(document.createElement('span'), {
        borderBottom: '14px solid transparent',
        borderLeft: '22px solid #fff',
        borderTop: '14px solid transparent',
        height: '0',
        left: '50%',
        position: 'absolute',
        top: '50%',
        transform: 'translate(-35%, -50%)',
        width: '0',
    });

const createPlayButton = () => {
    const playButton = applyStyles(document.createElement('span'), {
        backgroundColor: 'rgba(0, 0, 0, 0.6)',
        borderRadius: '12px',
        boxSizing: 'border-box',
        height: '64px',
        left: '50%',
        position: 'absolute',
        top: '50%',
        transform: 'translate(-50%, -50%)',
        width: '86px',
    });

    playButton.appendChild(createPlayIcon());

    return playButton;
};

const createYoutubeThumbnailPreview = (model, pointerEventsClass) => {
    const videoId = getYoutubeVideoId(model.get('videoId'));
    const preview = applyStyles(document.createElement('div'), {
        alignItems: 'center',
        backgroundColor: '#252525',
        display: 'flex',
        height: '100%',
        justifyContent: 'center',
        overflow: 'hidden',
        position: 'relative',
        width: '100%',
    });

    preview.className = `${pointerEventsClass} gjs-video-preview`;

    if (videoId) {
        const thumbnail = createYoutubeThumbnail();
        thumbnail.alt = '';
        thumbnail.src = `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;

        preview.appendChild(thumbnail);
    }

    preview.appendChild(createPlayButton());

    return preview;
};

const setYoutubePreviewDimensions = element =>
    applyStyles(element, {
        height: EDITOR_PREVIEW_HEIGHT,
        maxWidth: '100%',
        width: EDITOR_PREVIEW_WIDTH,
    });

const removeDeprecatedYoutubeTraits = traits =>
    traits.filter(({ name }) => !DEPRECATED_YOUTUBE_TRAIT_NAMES.includes(name));

const removeUrlParameter = (url, parameterName) =>
    url.replace(new RegExp(`([?&])${parameterName}=[^&]*`, 'g'), '').replace('?&', '?');

const escapeHtmlAttribute = value => String(value).replace(/"/g, '&quot;');

const getExportIframeSrc = model => {
    const provider = model.get('provider');

    if (provider === 'yt') {
        return model.getYoutubeSrc();
    }

    if (provider === 'ytnc') {
        return model.getYoutubeNoCookieSrc();
    }

    if (provider === 'vi') {
        return model.getVimeoSrc();
    }

    return '';
};

const createIframeHtml = model => {
    const attributes = {
        ...model.getAttributes(),
        src: getExportIframeSrc(model),
        allowfullscreen: 'allowfullscreen',
    };

    if (YOUTUBE_PROVIDERS.includes(model.get('provider'))) {
        attributes.referrerpolicy = YOUTUBE_REFERRER_POLICY;
        attributes.allow = YOUTUBE_ALLOW;
    }

    const htmlAttributes = Object.entries(attributes)
        .filter(([, value]) => value !== undefined && value !== null && value !== '')
        .map(([attribute, value]) => `${attribute}="${escapeHtmlAttribute(value)}"`)
        .join(' ');

    return `<iframe ${htmlAttributes}></iframe>`;
};

export default grapesjs.plugins.add('video', (editor, _options) => {
    const videoType = editor.DomComponents.getType('video');
    const videoModel = videoType.model;
    const videoView = videoType.view;

    editor.DomComponents.addType('video', {
        extend: 'video',
        isComponent: videoModel.isComponent,
        model: {
            getYoutubeTraits() {
                return removeDeprecatedYoutubeTraits(videoModel.prototype.getYoutubeTraits.call(this));
            },
            getYoutubeSrc() {
                return removeUrlParameter(videoModel.prototype.getYoutubeSrc.call(this), 'modestbranding');
            },
            toHTML() {
                if (this.get('provider') === 'vi' || YOUTUBE_PROVIDERS.includes(this.get('provider'))) {
                    return createIframeHtml(this);
                }

                return videoModel.prototype.toHTML.call(this);
            },
            getAttrToHTML() {
                const attr = videoModel.prototype.getAttrToHTML.call(this);

                if (YOUTUBE_PROVIDERS.includes(this.get('provider'))) {
                    attr.referrerpolicy = YOUTUBE_REFERRER_POLICY;
                    attr.allow = YOUTUBE_ALLOW;
                }

                return attr;
            },
        },
        view: {
            render() {
                videoView.prototype.render.call(this);

                if (YOUTUBE_PROVIDERS.includes(this.model.get('provider'))) {
                    setYoutubePreviewDimensions(this.el);
                }

                return this;
            },
            updateSrc() {
                if (YOUTUBE_PROVIDERS.includes(this.model.get('provider'))) {
                    this.updateProvider();
                    setYoutubePreviewDimensions(this.el);

                    return;
                }

                videoView.prototype.updateSrc.call(this);
            },
            renderYoutube() {
                return createYoutubeThumbnailPreview(this.model, `${this.ppfx}no-pointer`);
            },
            renderYoutubeNoCookie() {
                return createYoutubeThumbnailPreview(this.model, `${this.ppfx}no-pointer`);
            },
        },
    });

    editor.Blocks.add('video', {
        category: 'basic-objects',
        attributes: { class: 'fa fa-youtube-play' },
        content: {
            type: 'video',
            resizable: false,
        },
    });
});
