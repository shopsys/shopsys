import Translator from 'bazinga-translator';
import grapesjs from 'grapesjs';

export default grapesjs.plugins.add('video', (editor, _options) => {
    editor.Blocks.add('video', {
        label: Translator.trans('Video'),
        category: Translator.trans('Basic objects'),
        attributes: { class: 'fa fa-youtube-play' },
        content: {
            type: 'video',
        },
    });
});
