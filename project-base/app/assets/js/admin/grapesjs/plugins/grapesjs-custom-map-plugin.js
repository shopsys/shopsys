import Translator from 'bazinga-translator';
import grapesjs from 'grapesjs';

export default grapesjs.plugins.add('map', (editor, _options) => {
    editor.Blocks.add('map', {
        select: true,
        label: Translator.trans('Map'),
        category: Translator.trans('Basic objects'),
        attributes: { class: 'fa fa-map-o' },
        content: {
            type: 'map',
            style: { height: '350px', width: '100%' },
        },
    });
});
