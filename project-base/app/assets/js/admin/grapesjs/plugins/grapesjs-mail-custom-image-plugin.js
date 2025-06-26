import Translator from 'bazinga-translator';
import grapesjs from 'grapesjs';
import { linkPositionDataAttribute } from './grapesjs-custom-link-plugin';

export default grapesjs.plugins.add('mail-custom-image', editor => {
    const imagePositionDataAttribute = 'data-image-position';

    editor.Blocks.add('mail-custom-image', {
        select: true,
        activate: true,
        label: Translator.trans('Image'),
        category: Translator.trans('Basic objects'),
        attributes: { class: 'gjs-fonts gjs-f-image' },
        content: {
            type: 'mail-custom-image',
            attributes: {
                'data-gjs-type': 'mail-custom-image',
            },
        },
    });

    editor.DomComponents.addType('mail-custom-image', {
        isComponent: element =>
            element.tagName === 'IMG' &&
            element.getAttribute('data-gjs-type') === 'mail-custom-image' &&
            !element.hasAttribute('path'),
        extend: 'image',
        model: {
            init() {
                this.setStyle({});
                this.on('change:src', this.handlePathChange);
                this.on(`change:attributes:${imagePositionDataAttribute}`, this.handleImagePositionChange);
            },
            handlePathChange(element) {
                element.addAttributes({ path: this.attributes.src });
            },
            handleImagePositionChange(element) {
                element.setClass([`image-position-${this.getAttributes()[imagePositionDataAttribute]}`]);
                if (element.collection.parent.attributes.tagName === 'a') {
                    element.collection.parent.setAttributes({
                        [linkPositionDataAttribute]: this.getAttributes()[imagePositionDataAttribute],
                    });
                }
            },
            defaults: {
                attributes: {
                    [imagePositionDataAttribute]: 'left',
                    class: ['image-position-left'],
                },
                traits: [
                    {
                        type: 'text',
                        name: 'path',
                        label: Translator.trans('Path to file'),
                    },
                    {
                        type: 'select',
                        name: imagePositionDataAttribute,
                        label: Translator.trans('Position of image'),
                        options: [
                            {
                                id: 'left',
                                label: Translator.trans('Left'),
                            },
                            {
                                id: 'center',
                                label: Translator.trans('Center'),
                            },
                            {
                                id: 'right',
                                label: Translator.trans('Right'),
                            },
                        ],
                    },
                    {
                        type: 'input',
                        name: 'alt',
                        label: Translator.trans('Alt'),
                    },
                ],
                resizable: false,
            },
        },
    });

    editor.addStyle(`
        .image-position-center {
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .image-position-left {
            display: block;
            margin-left: 0;
            margin-right: auto;
        }

        .image-position-right {
            display: block;
            margin-left: auto;
            margin-right: 0;
        }
    `);
});
