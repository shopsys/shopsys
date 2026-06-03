import grapesjs from 'grapesjs';
import { column1BlockIcon } from '../shared/blockIcons';

export default grapesjs.plugins.add('column1', (editor, _options) => {
    editor.Blocks.add('column1', {
        category: 'basic-objects',
        attributes: { class: 'gjs-fonts gjs-f-b1' },
        media: column1BlockIcon,
        content: `
            <div class="row" data-gjs-droppable=".column">
                <div class="column"></div>
            </div>
        `,
    });
});
