import Translator from 'bazinga-translator';
import grapesjs from 'grapesjs';

export default grapesjs.plugins.add('column1', (editor, _options) => {
    editor.Blocks.add('column1', {
        label: Translator.trans('Column 1'),
        category: Translator.trans('Basic objects'),
        attributes: { class: 'gjs-fonts gjs-f-b1' },
        content: `
            <div class="row" data-gjs-droppable=".column">
                <div class="column"></div>
            </div>
        `,
    });
});
