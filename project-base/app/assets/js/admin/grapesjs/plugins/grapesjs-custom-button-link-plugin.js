import grapesjs from 'grapesjs';
import Translator from 'bazinga-translator';

const linkPositionDataAttribute = 'data-link-position';
const BUTTON_COLOR_ATTRIBUTE = 'backgroundColor';

export default grapesjs.plugins.add('custom-button-link', (editor) => {
    editor.Blocks.add('button-link', {
        id: 'button-link',
        label: Translator.trans('Button link'),
        type: 'Link',
        category: Translator.trans('Basic objects'),
        content:
            `<a data-gjs-type='button-link' class='gjs-button-link button-link-position-center'>
            <div class="gjs-text-ckeditor text">`
            + Translator.trans('Insert your text here')
            + `</div>
        </a>`,
        attributes: { class: 'fa fa-external-link' }
    });

    editor.DomComponents.addType('button-link', {
        isComponent: (element) => element.tagName === 'A',
        model: {
            init () {
                this.on(`change:attributes:${linkPositionDataAttribute}`, this.handleLinkPositionChange);
                this.on(`change:attributes:${BUTTON_COLOR_ATTRIBUTE}`, this.handleColorChange);
            },

            handleLinkPositionChange (element) {
                element.setClass([
                    'gjs-button-link',
                    `button-link-position-${this.getAttributes()[linkPositionDataAttribute]}`
                ]);
            },

            handleColorChange (component) {
                component.setStyle({
                    ...component.getStyle(),
                    'background-color': this.getAttributes()[BUTTON_COLOR_ATTRIBUTE].includes('#')
                        ? this.getAttributes()[BUTTON_COLOR_ATTRIBUTE]
                        : `#${this.getAttributes()[BUTTON_COLOR_ATTRIBUTE]}`,
                    'border-color': this.getAttributes()[BUTTON_COLOR_ATTRIBUTE].includes('#')
                        ? this.getAttributes()[BUTTON_COLOR_ATTRIBUTE]
                        : `#${this.getAttributes()[BUTTON_COLOR_ATTRIBUTE]}`
                });
            },

            defaults: {
                attributes: {
                    [linkPositionDataAttribute]: 'center',
                    [BUTTON_COLOR_ATTRIBUTE]: '#00C8B7',
                    class: ['button-link-position-center']
                },
                traits: [
                    {
                        type: 'input',
                        name: 'title',
                        label: Translator.trans('Title')
                    },
                    {
                        type: 'input',
                        name: 'href',
                        label: Translator.trans('Href')
                    },
                    {
                        type: 'checkbox',
                        name: 'target',
                        label: Translator.trans('Open in new window'),
                        valueTrue: '_blank',
                        valueFalse: ''
                    },
                    {
                        type: 'select',
                        name: linkPositionDataAttribute,
                        label: Translator.trans('Position of button'),
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
                        label: Translator.trans('Color of button'),
                        type: 'input',
                        name: BUTTON_COLOR_ATTRIBUTE
                    }
                ]
            }
        }
    });
});
