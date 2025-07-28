import grapesjs from 'grapesjs';

export default grapesjs.plugins.add('text', (editor, _options) => {
    editor.Blocks.add('text-ckeditor', {
        category: 'basic-objects',
        attributes: { class: 'gjs-fonts gjs-f-text' },
        content: { type: 'text-ckeditor', content: 'Insert your text here', activeOnRender: 1 },
    });
});
