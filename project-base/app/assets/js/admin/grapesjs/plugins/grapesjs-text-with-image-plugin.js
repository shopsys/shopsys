import grapesjs from 'grapesjs';
import Translator from 'bazinga-translator';

const IMAGE_POSITION_DATA_ATTRIBUTE = 'data-image-position';
const IMAGE_TYPE_DATA_ATTRIBUTE = 'data-image-type';

const TEXT_WITH_IMAGE_TYPE = 'text-with-image';

const IMAGE_CLASS = 'gjs-text-with-image';
const IMAGE_CLASS_INNER = 'gjs-text-with-image-inner';
const IMAGE_CLASS_TYPE = 'gjs-text-with-image-type';
const IMAGE_CLASS_FLOAT = 'gjs-text-with-image-float';

const IMAGE_POSITION_LEFT = 'left';
const IMAGE_POSITION_RIGHT = 'right';

const IMAGE_FLOAT_INSIDE = 'inside-layout';
const IMAGE_FLOAT_OUTSIDE = 'outside-layout';

export default grapesjs.plugins.add('text-with-image', editor => {
    editor.Blocks.add('textWithImage', {
        id: 'text-with-image',
        label: Translator.trans('Text with image'),
        category: 'Basic',
        media: '<svg xmlns="http://www.w3.org/2000/svg" width="48px" height="48px" viewBox="0 0 576 512"><path d="M528 32h-480C21.49 32 0 53.49 0 80V96h576V80C576 53.49 554.5 32 528 32zM0 432C0 458.5 21.49 480 48 480h480c26.51 0 48-21.49 48-48V128H0V432zM368 192h128C504.8 192 512 199.2 512 208S504.8 224 496 224h-128C359.2 224 352 216.8 352 208S359.2 192 368 192zM368 256h128C504.8 256 512 263.2 512 272S504.8 288 496 288h-128C359.2 288 352 280.8 352 272S359.2 256 368 256zM368 320h128c8.836 0 16 7.164 16 16S504.8 352 496 352h-128c-8.836 0-16-7.164-16-16S359.2 320 368 320zM176 192c35.35 0 64 28.66 64 64s-28.65 64-64 64s-64-28.66-64-64S140.7 192 176 192zM112 352h128c26.51 0 48 21.49 48 48c0 8.836-7.164 16-16 16h-192C71.16 416 64 408.8 64 400C64 373.5 85.49 352 112 352z"/></svg>',
        content: {
            type: TEXT_WITH_IMAGE_TYPE
        }
    });

    editor.DomComponents.addType(TEXT_WITH_IMAGE_TYPE, {
        isComponent: element => element.classList && element.classList.contains(IMAGE_CLASS),
        model: {
            defaults: {
                attributes: {
                    class: [IMAGE_CLASS]
                },
                droppable: false,
                components: `
                    <div class="${IMAGE_CLASS_INNER} ${IMAGE_CLASS_FLOAT}-left ${IMAGE_CLASS_TYPE}-outside-layout">
                        <img class="image">
                        <div class="gjs-text-ckeditor text">Insert your text here</div>
                    </div>
                `
            }
        }
    });

    editor.DomComponents.addType('text-with-image-inner', {
        isComponent: element => element.classList && element.classList.contains(IMAGE_CLASS_INNER),
        model: {
            init () {
                this.on(`change:attributes:${IMAGE_POSITION_DATA_ATTRIBUTE}`, this.handleTypeChange);
                this.on(`change:attributes:${IMAGE_TYPE_DATA_ATTRIBUTE}`, this.handleTypeChange);
            },

            handleTypeChange (element) {
                element.setClass([IMAGE_CLASS_INNER, `${IMAGE_CLASS_FLOAT}-${this.getAttributes()[IMAGE_POSITION_DATA_ATTRIBUTE]}`,
                    `${IMAGE_CLASS_TYPE}-${this.getAttributes()[IMAGE_TYPE_DATA_ATTRIBUTE]}`
                ]);
            },
            defaults: {
                removable: false,
                draggable: false,
                copyable: false,
                droppable: false,
                propagate: ['removable', 'draggable', 'copyable', 'droppable'],
                attributes: {
                    [IMAGE_POSITION_DATA_ATTRIBUTE]: IMAGE_POSITION_LEFT,
                    [IMAGE_TYPE_DATA_ATTRIBUTE]: IMAGE_FLOAT_OUTSIDE,
                    class: [IMAGE_CLASS_INNER]
                },
                traits: [
                    {
                        type: 'select',
                        name: IMAGE_POSITION_DATA_ATTRIBUTE,
                        label: Translator.trans('Position of image'),
                        options: [
                            {
                                id: IMAGE_POSITION_LEFT,
                                label: 'Left'
                            },
                            {
                                id: IMAGE_POSITION_RIGHT,
                                label: 'Right'
                            }
                        ]
                    },
                    {
                        type: 'select',
                        name: IMAGE_TYPE_DATA_ATTRIBUTE,
                        label: Translator.trans('Type of image'),
                        options: [
                            {
                                id: IMAGE_FLOAT_OUTSIDE,
                                label: Translator.trans('Outside layout')
                            },
                            {
                                id: IMAGE_FLOAT_INSIDE,
                                label: Translator.trans('Inside layout')
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
