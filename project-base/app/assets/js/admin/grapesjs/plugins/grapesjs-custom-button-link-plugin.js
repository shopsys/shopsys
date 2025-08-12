import Translator from 'bazinga-translator';
import grapesjs from 'grapesjs';

const linkPositionDataAttribute = 'data-link-position';
const BUTTON_COLOR_ATTRIBUTE = 'backgroundColor';

export default grapesjs.plugins.add('custom-button-link', editor => {
    editor.Blocks.add('button-link', {
        id: 'button-link',
        type: 'Link',
        category: 'basic-objects',
        content:
            `<div style="width: 100%">
                <a href="" data-gjs-type='button-link' class='gjs-button-link button-link-position-center'>`
                    + Translator.trans('Insert your text here') +
                `</a>
            </div>`,
        attributes: { class: 'fa fa-external-link' },
    });

    editor.DomComponents.addType('button-link', {
        isComponent: element => element.tagName === 'A',
        model: {
            init() {
                this.on(`change:attributes:${linkPositionDataAttribute}`, this.handleLinkPositionChange);
                this.on(`change:attributes:${BUTTON_COLOR_ATTRIBUTE}`, this.handleColorChange);
            },

            handleLinkPositionChange(element) {
                element.setClass([
                    'gjs-button-link',
                    `button-link-position-${this.getAttributes()[linkPositionDataAttribute]}`,
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

            defaults: {
                attributes: {
                    [linkPositionDataAttribute]: 'center',
                    [BUTTON_COLOR_ATTRIBUTE]: '#00C8B7',
                    class: ['button-link-position-center'],
                },
                traits: [
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
                        name: linkPositionDataAttribute,
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
