import grapesjs from 'grapesjs';

export default grapesjs.plugins.add('ckeditor', (editor, _options) => {
    editor.Components.addType('text-ckeditor', {
        isComponent: element => element.classList?.contains('gjs-text-ckeditor'),
        extend: 'text',
        model: {
            defaults: {
                attributes: {
                    class: ['gjs-text-ckeditor'],
                    'data-gjs-type': 'text',
                },
            },
        },
    });
});
