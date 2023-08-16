
import grapesjs from 'grapesjs';

export default grapesjs.plugins.add('custom-iframe', (editor) => {
    editor.DomComponents.addType('iframe', {
        isComponent: el => el.tagName === 'IFRAME',
        model: {
            defaults: {
                type: 'iframe',
                traits: [
                    {
                        type: 'text',
                        label: 'src',
                        name: 'src'
                    },
                    {
                        type: 'text',
                        label: 'Width',
                        name: 'width',
                        value: '100%'
                    },
                    {
                        type: 'text',
                        label: 'Height',
                        name: 'height'
                    }
                ]
            }
        }
    });

    editor.BlockManager.add('iframe', {
        label: 'Iframe',
        type: 'iframe',
        content: '<iframe> </iframe>',
        category: 'Basic',
        selectable: true,
        attributes: { class: 'fa fa-crop' }
    });
});
