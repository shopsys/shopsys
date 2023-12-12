import grapesjs from 'grapesjs';

export default grapesjs.plugins.add('custom-blocks', (editor, options) => {

    editor.Components.addType('text-ckeditor', {
        isComponent: element => element.classList && element.classList.contains('gjs-text-ckeditor'),
        extend: 'text',
        model: {
            defaults: {
                attributes: {
                    class: ['gjs-text-ckeditor'],
                    'data-gjs-type': 'text'
                }
            }
        }
    });

    editor.Blocks.add('column1', {
        label: 'Column 1',
        category: 'Basic',
        attributes: { class: 'gjs-fonts gjs-f-b1' },
        content: `
            <div class="row" data-gjs-droppable=".column">
                <div class="column"></div>
            </div>
        `
    });

    editor.Blocks.add('column2', {
        label: 'Column 2',
        category: 'Basic',
        attributes: { class: 'gjs-fonts gjs-f-b2' },
        content: `
            <div class="row" data-gjs-droppable=".column">
                <div class="column"></div>
                <div class="column"></div>
            </div>
        `
    });

    editor.Blocks.add('text-ckeditor', {
        label: 'Text',
        category: 'Basic',
        attributes: { class: 'gjs-fonts gjs-f-text' },
        content: { type: 'text-ckeditor', content: 'Insert your text here', activeOnRender: 1 }
    });

    editor.Blocks.add('video', {
        label: 'Video',
        category: 'Basic',
        attributes: { class: 'fa fa-youtube-play' },
        content: {
            type: 'video'
        }
    });

    editor.Blocks.add('map', {
        select: true,
        label: 'Map',
        category: 'Basic',
        attributes: { class: 'fa fa-map-o' },
        content: {
            type: 'map',
            style: { height: '350px', width: '100%' }
        }
    });
});
