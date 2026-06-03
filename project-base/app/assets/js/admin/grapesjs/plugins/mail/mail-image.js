import grapesjs from 'grapesjs';
import { imageBlockIcon } from '../shared/blockIcons';
import { LINK_POSITION_DATA_ATTRIBUTE } from '../web/link';

export default grapesjs.plugins.add('mail-custom-image', editor => {
    const imagePositionDataAttribute = 'data-image-position';

    editor.Blocks.add('mail-custom-image', {
        select: true,
        activate: true,
        category: 'basic-objects',
        attributes: { class: 'gjs-fonts gjs-f-image' },
        media: imageBlockIcon,
        content: {
            type: 'mail-custom-image',
            attributes: {
                'data-gjs-type': 'mail-custom-image',
            },
        },
    });

    editor.DomComponents.addType('mail-custom-image', {
        isComponent: element => {
            if (element.tagName !== 'IMG') {
                return false;
            }
            // Match by data-gjs-type (for new components)
            if (element.getAttribute('data-gjs-type') === 'mail-custom-image') {
                return element.getAttribute('path') !== '{product_image}';
            }
            // Match by class (for reloaded components after save)
            if (element.classList?.contains('mail-custom-image')) {
                return element.getAttribute('path') !== '{product_image}';
            }
            return false;
        },
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
                const position = this.getAttributes()[imagePositionDataAttribute];

                element.setClass(['mail-custom-image', `image-position-${position}`]);
                if (element.collection.parent.attributes.tagName === 'a') {
                    element.collection.parent.setAttributes({
                        [LINK_POSITION_DATA_ATTRIBUTE]: position,
                    });
                }
            },
            defaults: {
                attributes: {
                    [imagePositionDataAttribute]: 'left',
                    class: ['mail-custom-image', 'image-position-left'],
                },
                resizable: {
                    updateTarget: (el, rect) => {
                        const widthPx = `${Math.round(rect.w)}px`;
                        const heightPx = 'auto';

                        // Update DOM element immediately for visual feedback
                        el.style.width = widthPx;
                        el.style.height = heightPx;

                        // Get the component model and update its styles for persistence
                        const component = editor.getSelected();
                        if (component && component.getEl() === el) {
                            component.addStyle({
                                width: widthPx,
                                height: heightPx,
                            });
                        }
                    },
                },
                traits: [
                    {
                        type: 'text',
                        name: 'path',
                    },
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

    // Modify default image block for mail
    const mailImageBlock = editor.BlockManager.get('image');
    if (mailImageBlock) {
        mailImageBlock.attributes.content.style = { ...mailImageBlock.attributes.content.style, 'max-width': '100%' };
        editor.Blocks.remove('image');
    }

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
