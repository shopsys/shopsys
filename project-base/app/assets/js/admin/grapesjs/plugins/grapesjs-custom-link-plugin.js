import grapesjs from 'grapesjs';
import Translator from 'bazinga-translator';

export const linkPositionDataAttribute = 'data-link-position';
export default grapesjs.plugins.add('custom-link', (editor) => {

    editor.Blocks.add('link-block', {
        id: 'link-block',
        label: Translator.trans('Link Block'),
        category: 'Basic',
        content: `
          <a data-gjs-type="link-block" class="gjs-link-block"></a>
        `,
        attributes: { class: 'fa fa-link' }
    });

    editor.DomComponents.addType('link-block', {
        isComponent: (element) => element.tagName === 'A',
        model: {
            init () {
                this.on(`change:attributes:${linkPositionDataAttribute}`, this.handleLinkPositionChange);
            },

            handleLinkPositionChange (element) {
                element.setClass(['gjs-link-block', `image-position-${this.getAttributes()[linkPositionDataAttribute]}`]);
            },

            defaults: {
                attributes: {
                    [linkPositionDataAttribute]: 'left',
                    class: ['image-position-left']
                },
                traits: [
                    {
                        type: 'input',
                        name: 'href',
                        label: 'Href'
                    },
                    {
                        type: 'input',
                        name: 'title',
                        label: 'Title'
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
                        label: Translator.trans('Position of link'),
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
                    }
                ]
            }
        }
    });
});
