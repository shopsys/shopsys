import grapesjs from 'grapesjs';

export default grapesjs.plugins.add('video', (editor, _options) => {
    editor.Blocks.add('video', {
        category: 'basic-objects',
        attributes: { class: 'fa fa-youtube-play' },
        content: {
            type: 'video',
            resizable: false,
        },
    });
});
