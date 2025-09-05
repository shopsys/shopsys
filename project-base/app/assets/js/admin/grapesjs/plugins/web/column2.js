import grapesjs from 'grapesjs';

export default grapesjs.plugins.add('column2', (editor, _options) => {
    editor.Blocks.add('column2', {
        category: 'basic-objects',
        attributes: { class: 'gjs-fonts gjs-f-b2' },
        content: `
            <div class="row" data-gjs-droppable=".column">
                <div class="column"></div>
                <div class="column"></div>
            </div>
        `,
    });
});
