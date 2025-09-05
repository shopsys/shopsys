import Translator from 'bazinga-translator';
import grapesjs from 'grapesjs';

const IMAGE_POSITION_DATA_ATTRIBUTE = 'data-image-position';

const TEXT_WITH_IMAGE_TYPE = 'text-with-image';

const TEXT_WITH_IMAGE_CLASS = 'gjs-text-with-image';
const IMAGE_FLOAT_CLASS = 'gjs-text-with-image-float';

const IMAGE_POSITION_LEFT = 'left';
const IMAGE_POSITION_RIGHT = 'right';

export default grapesjs.plugins.add('text-with-image', editor => {
    editor.Blocks.add('textWithImage', {
        id: 'text-with-image',
        category: 'basic-objects',
        media: '<div class="gjs-text-with-image-icon-wrapper"><svg xmlns="http://www.w3.org/2000/svg" width="48px" height="48px" viewBox="0 0 576 512"><path d="M528 32h-480C21.49 32 0 53.49 0 80V96h576V80C576 53.49 554.5 32 528 32zM0 432C0 458.5 21.49 480 48 480h480c26.51 0 48-21.49 48-48V128H0V432zM368 192h128C504.8 192 512 199.2 512 208S504.8 224 496 224h-128C359.2 224 352 216.8 352 208S359.2 192 368 192zM368 256h128C504.8 256 512 263.2 512 272S504.8 288 496 288h-128C359.2 288 352 280.8 352 272S359.2 256 368 256zM368 320h128c8.836 0 16 7.164 16 16S504.8 352 496 352h-128c-8.836 0-16-7.164-16-16S359.2 320 368 320zM176 192c35.35 0 64 28.66 64 64s-28.65 64-64 64s-64-28.66-64-64S140.7 192 176 192zM112 352h128c26.51 0 48 21.49 48 48c0 8.836-7.164 16-16 16h-192C71.16 416 64 408.8 64 400C64 373.5 85.49 352 112 352z"/></svg></div>',
        content: {
            type: TEXT_WITH_IMAGE_TYPE,
        },
    });

    editor.DomComponents.addType(TEXT_WITH_IMAGE_TYPE, {
        isComponent: element => element.classList?.contains(TEXT_WITH_IMAGE_CLASS),
        model: {
            init() {
                this.on(`change:attributes:${IMAGE_POSITION_DATA_ATTRIBUTE}`, this.handlePositionChange);
            },

            handlePositionChange(element) {
                element.setClass([
                    TEXT_WITH_IMAGE_CLASS,
                    `${IMAGE_FLOAT_CLASS}-${this.getAttributes()[IMAGE_POSITION_DATA_ATTRIBUTE]}`,
                ]);
            },

            defaults: {
                droppable: false,
                attributes: {
                    [IMAGE_POSITION_DATA_ATTRIBUTE]: IMAGE_POSITION_LEFT,
                    class: [TEXT_WITH_IMAGE_CLASS, `${IMAGE_FLOAT_CLASS}-left`],
                },
                components: [
                    {
                        tagName: 'img',
                        type: 'image',
                        classes: ['image'],
                        attributes: {
                            'data-gjs-type': 'image',
                        },
                        removable: false,
                        draggable: false,
                        copyable: false,
                        resizable: {
                            tl: 1,
                            tr: 1,
                            bl: 1,
                            br: 1,
                            tc: 0,
                            bc: 0,
                            cl: 0,
                            cr: 0,
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
                    },
                    {
                        tagName: 'div',
                        type: 'text',
                        classes: ['gjs-text-ckeditor', 'text'],
                        content: Translator.trans('Insert your text here'),
                        attributes: {
                            'data-gjs-type': 'text',
                        },
                        removable: false,
                        draggable: false,
                        copyable: false,
                    },
                ],
                traits: [
                    {
                        type: 'select',
                        name: IMAGE_POSITION_DATA_ATTRIBUTE,
                        options: [
                            {
                                id: IMAGE_POSITION_LEFT,
                            },
                            {
                                id: IMAGE_POSITION_RIGHT,
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
