import grapesjs from 'grapesjs';
import { column3BlockIcon } from '../shared/blockIcons';

export default grapesjs.plugins.add('column3', (editor, _options) => {
    editor.Blocks.add('column3', {
        category: 'basic-objects',
        attributes: { class: 'gjs-fonts gjs-f-b3' },
        media: column3BlockIcon,
        content: `
            <div class="row" data-gjs-droppable=".column">
                <div class="column"></div>
                <div class="column"></div>
                <div class="column"></div>
            </div>
        `,
    });
});
