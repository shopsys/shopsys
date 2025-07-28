import grapesjs from 'grapesjs';
import { linkPositionDataAttribute } from './grapesjs-custom-link-plugin';

export default grapesjs.plugins.add('custom-image', editor => {
    const imagePositionDataAttribute = 'data-image-position';

    editor.Blocks.add('image', {
        select: true,
        activate: true,
        category: 'basic-objects',
        attributes: { class: 'gjs-fonts gjs-f-image' },
        content: {
            type: 'image',
            attributes: {
                'data-gjs-type': 'image',
            },
        },
    });

    editor.DomComponents.addType('image', {
        isComponent: element => element.tagName === 'IMG' && element.getAttribute('data-gjs-type') === 'image',
        extend: 'image',
        model: {
            init() {
                this.on(`change:attributes:${imagePositionDataAttribute}`, this.handleImagePositionChange);
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
                resizable: false,
                attributes: {
                    [imagePositionDataAttribute]: 'left',
                    class: ['image-position-left'],
                },
                traits: [
                    {
                        type: 'select',
                        name: imagePositionDataAttribute,
                        options: [
                            {
                                id: 'left',
                            },
                            {
                                id: 'center',
                            },
                            {
                                id: 'right',
                            },
                        ],
                    },
                    {
                        type: 'input',
                        name: 'alt',
                    },
                ],
            },
        },
    });
});
