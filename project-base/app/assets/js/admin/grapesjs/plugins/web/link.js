import grapesjs from 'grapesjs';

export const linkPositionDataAttribute = 'data-link-position';
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
                this.on(`change:attributes:${linkPositionDataAttribute}`, this.handleLinkPositionChange);
            },

            handleLinkPositionChange(element) {
                element.setClass([
                    'gjs-link-block',
                    `image-position-${this.getAttributes()[linkPositionDataAttribute]}`,
                ]);
            },

            defaults: {
                attributes: {
                    [linkPositionDataAttribute]: 'left',
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
                ],
            },
        },
    });
});
