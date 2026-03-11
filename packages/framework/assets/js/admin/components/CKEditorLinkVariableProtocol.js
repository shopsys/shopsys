// When the CKEditor link dialog URL contains a template variable (e.g. {order_detail_url}),
// automatically switch the protocol dropdown to <other> so it doesn't get prefixed with http://
import Register from '../../common/utils/Register';

function initCKEditorLinkVariableProtocol() {
    if (typeof CKEDITOR === 'undefined') {
        return;
    }

    if (CKEDITOR._linkProtocolFixRegistered) {
        return;
    }
    CKEDITOR._linkProtocolFixRegistered = true;

    function switchProtocolIfNeeded(dialog) {
        var urlField = dialog.getContentElement('info', 'url');
        var protocolField = dialog.getContentElement('info', 'protocol');
        var url;
        if (urlField && protocolField) {
            url = urlField.getValue();
            if (url && url.indexOf('{') !== -1 && protocolField.getValue() !== '') {
                protocolField.setValue('');
            }
        }
    }

    CKEDITOR.on('dialogDefinition', ev => {
        if (ev.data.name !== 'link') {
            return;
        }

        var originalOnShow = ev.data.definition.onShow;
        ev.data.definition.onShow = function () {
            var urlField;
            var inputEl;

            if (originalOnShow) {
                originalOnShow.call(this);
            }

            switchProtocolIfNeeded(this);

            urlField = this.getContentElement('info', 'url');
            if (urlField && !urlField._protocolFixPatched) {
                urlField._protocolFixPatched = true;

                inputEl = urlField.getInputElement().$;
                inputEl.addEventListener('keyup', () => {
                    switchProtocolIfNeeded(this);
                });
            }
        };
    });
}

new Register().registerCallback(initCKEditorLinkVariableProtocol, 'CKEditorLinkVariableProtocol');
