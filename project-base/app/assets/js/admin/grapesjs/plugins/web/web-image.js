import grapesjs from 'grapesjs';
import { imageBlockIcon } from '../shared/blockIcons';
import { LINK_POSITION_DATA_ATTRIBUTE } from './link';

const IMAGE_POSITION_DATA_ATTRIBUTE = 'data-image-position';

export default grapesjs.plugins.add('custom-image', editor => {
    editor.Blocks.add('image', {
        select: true,
        activate: true,
        category: 'basic-objects',
        attributes: { class: 'gjs-fonts gjs-f-image' },
        media: imageBlockIcon,
        content: {
            type: 'image',
            attributes: {
                'data-gjs-type': 'image',
            },
        },
    });

    editor.DomComponents.addType('image', {
        isComponent: element => {
            if (element.tagName !== 'IMG') {
                return false;
            }
            // Match by data-gjs-type (for new components)
            if (element.getAttribute('data-gjs-type') === 'image') {
                return true;
            }
            // Match by class (for reloaded components after save)
            if (element.classList?.contains('web-custom-image')) {
                return true;
            }
            return false;
        },
        extend: 'image',
        model: {
            init() {
                this.on(`change:attributes:${IMAGE_POSITION_DATA_ATTRIBUTE}`, this.handleImagePositionChange);
            },
            handleImagePositionChange(element) {
                const position = this.getAttributes()[IMAGE_POSITION_DATA_ATTRIBUTE];

                element.setClass(['web-custom-image', `image-position-${position}`]);
                if (element.collection.parent.attributes.tagName === 'a') {
                    element.collection.parent.setAttributes({
                        [LINK_POSITION_DATA_ATTRIBUTE]: position,
                    });
                }
            },
            defaults: {
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
                attributes: {
                    'data-gjs-type': 'image',
                    [IMAGE_POSITION_DATA_ATTRIBUTE]: 'left',
                    class: ['web-custom-image', 'image-position-left'],
                },
                traits: [
                    {
                        type: 'select',
                        name: IMAGE_POSITION_DATA_ATTRIBUTE,
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
