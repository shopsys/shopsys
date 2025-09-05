import Translator from 'bazinga-translator';
import grapesjs from 'grapesjs';

export default grapesjs.plugins.add('mail-text', editor => {
    editor.Blocks.add('text-ckeditor', {
        category: 'basic-objects',
        attributes: { class: 'gjs-fonts gjs-f-text' },
        content: { type: 'text-ckeditor', content: Translator.trans('Insert your text here'), activeOnRender: 1 },
    });
});
