import grapesjs from 'grapesjs';

export default grapesjs.plugins.add('image-file', editor => {
    editor.Blocks.add('image-file', {
        select: true,
        activate: true,
        category: 'basic-objects',
        attributes: { class: 'fa fa-regular fa-file' },
        content: {
            type: 'image-file',
            class: 'gjs-image-block',
        },
    });

    editor.DomComponents.addType('image-file', {
        isComponent: element => element.tagName === 'img',
        extend: 'image',
        model: {
            init() {
                this.on('change:src', this.handlePathChange);
            },

            handlePathChange(element) {
                element.addAttributes({ path: this.attributes.src });
            },

            defaults: {
                traits: [
                    {
                        type: 'text',
                        name: 'path',
                    },
                ],
            },
        },
    });
});
