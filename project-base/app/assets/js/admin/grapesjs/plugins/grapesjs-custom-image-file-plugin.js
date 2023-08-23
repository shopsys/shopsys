
import grapesjs from 'grapesjs';
import Translator from 'bazinga-translator';

export default grapesjs.plugins.add('custom-image-file', (editor) => {
    editor.Blocks.add('image-file', {
        select: true,
        activate: true,
        label: Translator.trans('File'),
        category: 'Basic',
        attributes: { class: 'fa fa-regular fa-file' },
        content: {
            type: 'image-file',
            class: 'gjs-image-block'
        }
    });

    editor.DomComponents.addType('image-file', {
        isComponent: (element) => element.tagName === 'img',
        extend: 'image',
        model: {
            init () {
                this.on('change:src', this.handlePathChange);
            },

            handlePathChange (element) {
                element.addAttributes({ path: this.attributes.src });
            },

            defaults: {
                traits: [
                    {
                        type: 'text',
                        name: 'path',
                        label: Translator.trans('Path to file')
                    }
                ]
            }
        }
    });
});
