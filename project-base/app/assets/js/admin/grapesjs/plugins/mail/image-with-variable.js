import grapesjs from 'grapesjs';

export default grapesjs.plugins.add('mail-custom-image-with-variable', editor => {
    editor.Blocks.add('mail-custom-image-with-variable', {
        select: true,
        activate: true,
        category: 'basic-objects',
        attributes: { class: 'gjs-fonts gjs-f-image' },
        content: {
            type: 'mail-custom-image-with-variable',
            tagName: 'img',
            attributes: {
                src: '',
                path: '{product_image}',
                style: 'display: block; margin: auto;',
                'data-gjs-type': 'mail-custom-image-with-variable',
            },
        },
    });

    editor.DomComponents.addType('mail-custom-image-with-variable', {
        isComponent: element =>
            element.tagName === 'IMG' &&
            element.getAttribute('data-gjs-type') === 'mail-custom-image-with-variable' &&
            element.hasAttribute('path') &&
            element.getAttribute('path') === '{product_image}',
        extend: 'image',
        model: {
            init() {
                this.set('void', true);
            },
            defaults: {
                tagName: 'img',
                attributes: {
                    src: '',
                    path: '{product_image}',
                    style: 'display: block; margin: auto;',
                    class: 'mail-custom-image-with-variable',
                },
                editable: false,
                highlightable: true,
                resizable: false,
            },
        },
    });
});
