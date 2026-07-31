import grapesjs from 'grapesjs';
import { addRelNoopenerToComponentWithBlankTarget } from '../shared/linkNoopener';

export const LINK_POSITION_DATA_ATTRIBUTE = 'data-link-position';

export default grapesjs.plugins.add('link', editor => {
    editor.Blocks.add('link-block', {
        id: 'link-block',
        category: 'basic-objects',
        content: `
          <a data-gjs-type="link-block" class="gjs-link-block"></a>`,
        attributes: { class: 'fa fa-link' },
    });

    editor.DomComponents.addType('link-block', {
        isComponent: element => element.tagName === 'A',
        model: {
            init() {
                this.on(`change:attributes:${LINK_POSITION_DATA_ATTRIBUTE}`, this.handleLinkPositionChange);
                this.on('change:attributes:target', addRelNoopenerToComponentWithBlankTarget);

                addRelNoopenerToComponentWithBlankTarget(this);
            },

            handleLinkPositionChange(element) {
                element.setClass([
                    'gjs-link-block',
                    `image-position-${this.getAttributes()[LINK_POSITION_DATA_ATTRIBUTE]}`,
                ]);
            },

            defaults: {
                attributes: {
                    [LINK_POSITION_DATA_ATTRIBUTE]: 'left',
                    class: ['image-position-left'],
                },
                traits: [
                    {
                        type: 'input',
                        name: 'href',
                    },
                    {
                        type: 'input',
                        name: 'title',
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
                ],
            },
        },
    });
});
