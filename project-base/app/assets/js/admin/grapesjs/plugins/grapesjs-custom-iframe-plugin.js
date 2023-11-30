
import grapesjs from 'grapesjs';

const IFRAME_WIDTH_ATTRIBUTE = 'width';
const IFRAME_HEIGHT_ATTRIBUTE = 'height';

export default grapesjs.plugins.add('custom-iframe', (editor) => {
    editor.DomComponents.addType('iframe', {
        isComponent: el => el.tagName === 'IFRAME',
        model: {
            init () {
                this.on(`change:attributes:${IFRAME_WIDTH_ATTRIBUTE}`, this.handleWidthChange);
                this.on(`change:attributes:${IFRAME_HEIGHT_ATTRIBUTE}`, this.handleHeightChange);
            },
            handleWidthChange (component) {
                console.log('Input width changed to: ', this.getAttributes()[IFRAME_WIDTH_ATTRIBUTE], component.getStyle(), component);
                component.setStyle({ ...component.getStyle(), width: this.getAttributes()[IFRAME_WIDTH_ATTRIBUTE] });
                // component.setStyle({ ...component.getStyle(), id: 'width', 'data-key': this.getAttributes()[IFRAME_WIDTH_ATTRIBUTE] });
            },
            handleHeightChange (component) {
                console.log('Input height changed to: ', this.getAttributes()[IFRAME_HEIGHT_ATTRIBUTE], component);
                component.setStyle({ ...component.getStyle(), height: this.getAttributes()[IFRAME_HEIGHT_ATTRIBUTE] });
                // component.setAttributes({ height: this.getAttributes()[IFRAME_HEIGHT_ATTRIBUTE] });
            },
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
                        name: IFRAME_WIDTH_ATTRIBUTE,
                        placeholder: '100%'
                    },
                    {
                        type: 'text',
                        label: 'Height',
                        name: IFRAME_HEIGHT_ATTRIBUTE
                    }
                ]
            }
        }
    });

    editor.BlockManager.add('iframe', {
        label: 'Iframe',
        type: 'iframe',
        content: '<iframe class="gjs-iframe" style="width: 100%"> </iframe>',
        category: 'Basic',
        selectable: true,
        attributes: { class: 'fa fa-crop' }
    });
});
