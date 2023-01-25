import grapesjs from 'grapesjs';
import FormChangeInfo from 'framework/admin/components/FormChangeInfo';

const BUTTON_CLOSE = 'close';
const BUTTON_SAVE = 'save';

export default grapesjs.plugins.add('customButtons', (editor, options) => {

    const panels = editor.Panels;
    const textareaId = options.textareaId;

    panels.removeButton('options', 'fullscreen');
    panels.removeButton('options', 'export-template');
    panels.removeButton('options', 'gjs-open-import-webpage');
    panels.removeButton('options', 'canvas-clear');

    panels.addButton('options', {
        id: BUTTON_SAVE,
        context: BUTTON_SAVE,
        className: 'fa fa-save',
        command (editor) {
            const html = editor.getHtml();
            let styles = editor.getStyle();
            const filteredStyles = JSON.parse(JSON.stringify(styles)).filter(function (node) {
                const containsCorrectSelector = node.selectors !== undefined
                    && node.selectors.length !== 0
                    && typeof node.selectors[0] !== 'object'
                    && (node.selectors[0].startsWith('#') || node.selectors[0].startsWith('gcss'));

                const containsCorrectAddSelector = node.selectorsAdd !== undefined && node.selectorsAdd.startsWith('.gcss');

                return containsCorrectSelector || containsCorrectAddSelector;
            });
            editor.setStyle(filteredStyles);
            const css = editor.getCss();

            const exported = '<style>' + css + '</style>' + html;
            $('#' + textareaId).val(exported);
            $('body').removeAttr('style');
            $('#grapesjs').removeAttr('style').removeAttr('class');
            editor.destroy();
            FormChangeInfo.showInfo();
        }
    });

    panels.addButton('options', {
        id: BUTTON_CLOSE,
        context: BUTTON_CLOSE,
        className: 'fa fa-times',
        command (editor) {
            $('body').removeAttr('style');
            $('#grapesjs').removeAttr('style').removeAttr('class');
            editor.destroy();
        }
    });
});
