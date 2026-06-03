import grapesjs from 'grapesjs';
import { textBlockIcon } from '../shared/blockIcons';

export default grapesjs.plugins.add('text', (editor, _options) => {
    editor.Blocks.add('text-ckeditor', {
        category: 'basic-objects',
        attributes: { class: 'gjs-fonts gjs-f-text' },
        media: textBlockIcon,
        content: { type: 'text-ckeditor', content: 'Insert your text here', activeOnRender: 1 },
    });
});
