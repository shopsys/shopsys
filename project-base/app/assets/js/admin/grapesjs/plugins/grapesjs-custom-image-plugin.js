import grapesjs from 'grapesjs';
import Translator from 'bazinga-translator';
import { linkPositionDataAttribute } from './grapesjs-custom-link-plugin';

export default grapesjs.plugins.add('custom-image', (editor) => {
    const imagePositionDataAttribute = 'data-image-position';

    editor.Blocks.add('image', {
        select: true,
        activate: true,
        label: Translator.trans('Image'),
        category: 'Basic',
        attributes: { class: 'gjs-fonts gjs-f-image' },
        content: {
            type: 'image'
        }
    });

    editor.DomComponents.addType('image', {
        isComponent: (element) => element.tagName === 'IMG',
        extend: 'image',
        model: {
            init () {
                this.on(`change:attributes:${imagePositionDataAttribute}`, this.handleImagePositionChange);
            },

            handleImagePositionChange (element) {
                element.setClass([`image-position-${this.getAttributes()[imagePositionDataAttribute]}`]);
                if (element.collection.parent.attributes.tagName === 'a') {
                    element.collection.parent.setAttributes({ [linkPositionDataAttribute]: this.getAttributes()[imagePositionDataAttribute] });
                }
            },

            defaults: {
                attributes: {
                    [imagePositionDataAttribute]: 'left',
                    class: ['image-position-left']
                },
                traits: [
                    {
                        type: 'select',
                        name: imagePositionDataAttribute,
                        label: Translator.trans('Position of image'),
                        options: [
                            {
                                id: 'left',
                                label: Translator.trans('Left')
                            },
                            {
                                id: 'center',
                                label: Translator.trans('Center')
                            },
                            {
                                id: 'right',
                                label: Translator.trans('Right')
                            }
                        ]
                    },
                    {
                        type: 'input',
                        name: 'alt',
                        label: 'Alt'
                    }
                ]
            }
        }
    });
});
