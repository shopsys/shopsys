import grapesjs from 'grapesjs';

export default grapesjs.plugins.add('nonEditablePage', (editor, options) => {

    const Components = editor.Components;

    // Update the main wrapper
    Components.addType('wrapper', {
        model: {
            defaults: {
                selectable: false,
                highlightable: false,
                droppable: false,
                propagate: ['highlightable', 'selectable', 'droppable']
            },
            // Return always the content of editable content (defined below)
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

    // Create the editable component
    Components.addType('editable', {
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
