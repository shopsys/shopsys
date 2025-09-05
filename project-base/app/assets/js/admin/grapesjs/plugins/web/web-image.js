import grapesjs from 'grapesjs';
import { linkPositionDataAttribute } from './link';

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
