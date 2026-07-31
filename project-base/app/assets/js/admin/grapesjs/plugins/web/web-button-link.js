import Translator from 'bazinga-translator';
import grapesjs from 'grapesjs';
import { addRelNoopenerToComponentWithBlankTarget } from '../shared/linkNoopener';

const LINK_POSITION_DATA_ATTRIBUTE = 'data-link-position';
const BUTTON_COLOR_ATTRIBUTE = 'backgroundColor';
const TEXT_DATA_ATTRIBUTE = 'data-text';

export default grapesjs.plugins.add('web-button-link', editor => {
    editor.Blocks.add('button-link', {
        id: 'button-link',
        type: 'Link',
        category: 'basic-objects',
        content:
            `<a data-gjs-type='button-link' class='gjs-button-link button-link-position-center'>` +
            Translator.trans('Insert your text here') +
            `</a>`,
        attributes: { class: 'fa fa-external-link' },
    });

    editor.DomComponents.addType('button-link', {
        isComponent: element => element.tagName === 'A',
        model: {
            init() {
                this.on(`change:attributes:${LINK_POSITION_DATA_ATTRIBUTE}`, this.handleLinkPositionChange);
                this.on(`change:attributes:${BUTTON_COLOR_ATTRIBUTE}`, this.handleColorChange);
                this.on(`change:attributes:${TEXT_DATA_ATTRIBUTE}`, this.handleTextAttributeChange);
                this.on('change:attributes:target', addRelNoopenerToComponentWithBlankTarget);

                addRelNoopenerToComponentWithBlankTarget(this);
            },

            handleLinkPositionChange(element) {
                element.setClass([
                    'gjs-button-link',
                    `button-link-position-${this.getAttributes()[LINK_POSITION_DATA_ATTRIBUTE]}`,
                ]);
            },

            handleColorChange(component) {
                component.setStyle({
                    ...component.getStyle(),
                    'background-color': this.getAttributes()[BUTTON_COLOR_ATTRIBUTE].includes('#')
                        ? this.getAttributes()[BUTTON_COLOR_ATTRIBUTE]
                        : `#${this.getAttributes()[BUTTON_COLOR_ATTRIBUTE]}`,
                    'border-color': this.getAttributes()[BUTTON_COLOR_ATTRIBUTE].includes('#')
                        ? this.getAttributes()[BUTTON_COLOR_ATTRIBUTE]
                        : `#${this.getAttributes()[BUTTON_COLOR_ATTRIBUTE]}`,
                });
            },

            handleTextAttributeChange(_element) {
                const newText = this.getAttributes()[TEXT_DATA_ATTRIBUTE];
                this.components(newText);
            },

            defaults: {
                attributes: {
                    [LINK_POSITION_DATA_ATTRIBUTE]: 'center',
                    [BUTTON_COLOR_ATTRIBUTE]: '#00C8B7',
                    [TEXT_DATA_ATTRIBUTE]: 'Insert your text here',
                    class: ['button-link-position-center'],
                },
                traits: [
                    {
                        type: 'input',
                        name: TEXT_DATA_ATTRIBUTE,
                    },
                    {
                        type: 'input',
                        name: 'title',
                    },
                    {
                        type: 'input',
                        name: 'href',
                    },
                    {
                        type: 'checkbox',
                        name: 'target',
                        valueTrue: '_blank',
                        valueFalse: '',
                    },
                    {
                        type: 'select',
                        name: LINK_POSITION_DATA_ATTRIBUTE,
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
                        name: BUTTON_COLOR_ATTRIBUTE,
                    },
                ],
            },
        },
    });
});
