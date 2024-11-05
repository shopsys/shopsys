import grapesjs from 'grapesjs';
import Translator from 'bazinga-translator';

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
        label: Translator.trans('Column 1'),
        category: Translator.trans('Basic objects'),
        attributes: { class: 'gjs-fonts gjs-f-b1' },
        content: `
            <div class="row" data-gjs-droppable=".column">
                <div class="column"></div>
            </div>
        `
    });

    editor.Blocks.add('column2', {
        label: Translator.trans('Column 2'),
        category: Translator.trans('Basic objects'),
        attributes: { class: 'gjs-fonts gjs-f-b2' },
        content: `
            <div class="row" data-gjs-droppable=".column">
                <div class="column"></div>
                <div class="column"></div>
            </div>
        `
    });

    editor.Blocks.add('text-ckeditor', {
        label: Translator.trans('Text'),
        category: Translator.trans('Basic objects'),
        attributes: { class: 'gjs-fonts gjs-f-text' },
        content: { type: 'text-ckeditor', content: 'Insert your text here', activeOnRender: 1 }
    });

    editor.Blocks.add('video', {
        label: Translator.trans('Video'),
        category: Translator.trans('Basic objects'),
        attributes: { class: 'fa fa-youtube-play' },
        content: {
            type: 'video'
        }
    });

    editor.Blocks.add('map', {
        select: true,
        label: Translator.trans('Map'),
        category: Translator.trans('Basic objects'),
        attributes: { class: 'fa fa-map-o' },
        content: {
            type: 'map',
            style: { height: '350px', width: '100%' }
        }
    });
});
