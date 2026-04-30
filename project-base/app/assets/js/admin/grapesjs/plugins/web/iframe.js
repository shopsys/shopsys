import grapesjs from 'grapesjs';

const IFRAME_WIDTH_ATTRIBUTE = 'width';
const IFRAME_HEIGHT_ATTRIBUTE = 'height';
const VIDEO_EMBED_URL_PATTERN = /(youtube(-nocookie)?\.com\/embed|player\.vimeo\.com\/video)/;

const isVideoEmbed = element => VIDEO_EMBED_URL_PATTERN.test(element.getAttribute('src') ?? '');

export default grapesjs.plugins.add('iframe', editor => {
    editor.DomComponents.addType('iframe', {
        isComponent: element => element.tagName?.toUpperCase() === 'IFRAME' && !isVideoEmbed(element),
        model: {
            init() {
                this.on(`change:attributes:${IFRAME_WIDTH_ATTRIBUTE}`, this.handleWidthChange);
                this.on(`change:attributes:${IFRAME_HEIGHT_ATTRIBUTE}`, this.handleHeightChange);
            },
            handleWidthChange(component) {
                component.setStyle({ ...component.getStyle(), width: this.getAttributes()[IFRAME_WIDTH_ATTRIBUTE] });
            },
            handleHeightChange(component) {
                component.setStyle({ ...component.getStyle(), height: this.getAttributes()[IFRAME_HEIGHT_ATTRIBUTE] });
            },
            defaults: {
                type: 'iframe',
                resizable: false,
                traits: [
                    {
                        type: 'text',
                        name: 'src',
                    },
                    {
                        type: 'text',
                        name: IFRAME_WIDTH_ATTRIBUTE,
                        placeholder: '100%',
                    },
                    {
                        type: 'text',
                        name: IFRAME_HEIGHT_ATTRIBUTE,
                    },
                ],
            },
        },
    });

    editor.BlockManager.add('iframe', {
        type: 'iframe',
        content: '<iframe class="gjs-iframe" style="width: 100%"></iframe>',
        category: 'basic-objects',
        selectable: true,
        attributes: { class: 'fa fa-crop' },
    });
});
