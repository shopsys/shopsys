import Translator from 'bazinga-translator';
import grapesjs from 'grapesjs';
import { addRelNoopenerToComponentWithBlankTarget } from '../shared/linkNoopener';

const LINK_POSITION_DATA_ATTRIBUTE = 'data-link-position';
const BUTTON_COLOR_ATTRIBUTE = 'backgroundColor';

export default grapesjs.plugins.add('mail-button-link', editor => {
    editor.Blocks.add('button-link', {
        id: 'button-link',
        category: 'basic-objects',
        content:
            `<a data-gjs-type='button-link'
                style="
                margin: 0.75rem auto; 
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
            `</a>`,
        attributes: { class: 'fa fa-external-link' },
    });

    editor.DomComponents.addType('button-link', {
        isComponent: element => {
            if (element.tagName !== 'A') return false;

            return (
                element.getAttribute('data-gjs-type') === 'button-link' ||
                element.getAttribute('data-link-position') !== null ||
                element.classList?.contains('gjs-button-link')
            );
        },
        extend: 'link',

        model: {
            init() {
                this.on(`change:attributes:${LINK_POSITION_DATA_ATTRIBUTE}`, this.handleLinkPositionChange);
                this.on(`change:attributes:${BUTTON_COLOR_ATTRIBUTE}`, this.handleColorChange);
                this.on('change:attributes:target', addRelNoopenerToComponentWithBlankTarget);

                addRelNoopenerToComponentWithBlankTarget(this);
            },

            handleLinkPositionChange(component) {
                component.setStyle({
                    ...component.getStyle(),
                    margin:
                        this.getAttributes()[LINK_POSITION_DATA_ATTRIBUTE] === 'center'
                            ? '0.75rem auto'
                            : this.getAttributes()[LINK_POSITION_DATA_ATTRIBUTE] === 'right'
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

            defaults: {
                editable: true,
                droppable: false,
                attributes: {
                    [LINK_POSITION_DATA_ATTRIBUTE]: 'center',
                    [BUTTON_COLOR_ATTRIBUTE]: '#00C8B7',
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
