import grapesjs from 'grapesjs';

export default grapesjs.plugins.add('mail-template', editor => {
    editor.DomComponents.addType('wrapper', {
        model: {
            defaults: {
                selectable: false,
                highlightable: false,
                droppable: false,
                propagate: ['highlightable', 'selectable', 'droppable']
            },
            toHTML (opts) {
                const editable = this.findType('editable')[0];
                return editable ? editable.getInnerHTML() : '';
            }
        },
        view: {
            onRender ({ el }) {
                el.style.pointerEvents = 'none';
            }
        }
    });

    editor.DomComponents.addType('editable', {
        isComponent: element => element.classList && element.classList.contains('gjs-editable'),
        model: {
            defaults: {
                removable: false,
                draggable: false,
                copyable: false,
                propagate: []
            }
        },
        view: {
            onRender ({ el }) {
                el.style.pointerEvents = 'all';
            }
        }
    });

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

    editor.Blocks.add('text-ckeditor', {
        label: 'Text',
        category: 'Basic',
        attributes: { class: 'gjs-fonts gjs-f-text' },
        content: { type: 'text-ckeditor', content: 'Insert your text here', activeOnRender: 1 }
    });

    // add style to default image block
    const imageBlock = editor.BlockManager.get('image');
    imageBlock.attributes.content.style = { ...imageBlock.attributes.content.style, 'max-width': '100%' };

    // Patch for getCss to return always the content
    // from editable component
    editor.getModel().getCss = () => {
        const wrapper = editor.getWrapper();
        const cmp = wrapper.findType('editable')[0];
        return cmp ? editor.CodeManager.getCode(cmp, 'css') : '';
    };

    // Patch for layers root
    editor.on('run:core:open-layers', () => {
        const wrapper = Components.getWrapper();
        const editable = wrapper.findType('editable')[0];
        editable && editor.Layers.setRoot(editable);
    });
});
