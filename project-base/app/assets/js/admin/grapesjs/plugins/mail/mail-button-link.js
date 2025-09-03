import Translator from 'bazinga-translator';
import grapesjs from 'grapesjs';

const linkPositionDataAttribute = 'data-link-position';
const BUTTON_COLOR_ATTRIBUTE = 'backgroundColor';
const textDataAttribute = 'data-text';

export default grapesjs.plugins.add('mail-button-link', editor => {
    editor.Blocks.add('button-link', {
        id: 'button-link',
        category: 'basic-objects',
        content:
            `<div style="width: 100%">
                <a data-gjs-type='button-link'
                    style="
                    margin: 0.75rem auto; 
                    display: block; 
                    height: fit-content; 
                    width: fit-content; 
                    cursor: pointer; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                    gap: 0.5rem; 
                    border-radius: 0.5rem; 
                    border: 2px solid #00C8B7; 
                    background-color: #00C8B7; 
                    padding: 7px 12px; 
                    text-align: center; 
                    font-weight: 500; 
                    line-height: 18px; 
                    text-decoration: none; 
                    outline: none; 
                    transition: all 0.2s ease;
                    color: #fff;
                ">` +
            Translator.trans('Insert your text here') +
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
                this.on(`change:attributes:${textDataAttribute}`, this.handleTextAttributeChange);
            },

            handleLinkPositionChange(component) {
                component.setStyle({
                    ...component.getStyle(),
                    margin:
                        this.getAttributes()[linkPositionDataAttribute] === 'center'
                            ? '0.75rem auto'
                            : this.getAttributes()[linkPositionDataAttribute] === 'right'
                              ? '0.75rem 0 0.75rem auto'
                              : '0.75rem auto 0.75rem 0',
                });
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
                const newText = this.getAttributes()[textDataAttribute];
                this.components(newText);
            },

            defaults: {
                attributes: {
                    [linkPositionDataAttribute]: 'center',
                    [BUTTON_COLOR_ATTRIBUTE]: '#00C8B7',
                    [textDataAttribute]: 'Insert your text here',
                },
                traits: [
                    {
                        type: 'input',
                        name: textDataAttribute,
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
                        type: 'checkbox',
                        name: 'target',
                        valueTrue: '_blank',
                        valueFalse: '',
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
