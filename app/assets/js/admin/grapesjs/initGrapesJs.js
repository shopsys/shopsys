import Register from 'framework/common/utils/Register';
import grapesjs from 'grapesjs';
import 'grapesjs-preset-webpage';
import './plugins/grapesjs-custom-buttons-plugin';
import './plugins/grapesjs-products-plugin';
import './grapesjs-non-editable-page';

class Grapesjs {

    static init ($container) {
        // eslint-disable-next-line no-new
        new Grapesjs($container);
    }

    constructor ($container) {
        let isAnyButtonOnPage = false;
        $container.filterAllNodes('.js-grapesjs-button').each((index, element) => {
            $(element).on('click', event => {
                const frontendUrl = $(element).data('template-url');
                const textareaId = $(element).data('textarea-id');
                this.openGrapesEditor(event, frontendUrl, textareaId);
            });

            isAnyButtonOnPage = true;
        });

        if (isAnyButtonOnPage === true) {
            $('body').append('<div id="grapesjs"></div>');
        }
    }

    openGrapesEditor (event, frontendUrl, textareaId) {
        $('body').css({
            overflow: 'hidden',
            height: '100%'
        });

        const content = $.get({
            url: frontendUrl,
            async: false
        }).responseText;

        const editor = grapesjs.init({
            container: '#grapesjs',
            components: content,
            height: '100%',
            width: '100%',
            storageManager: false,
            noticeOnUnload: false,
            exportWrapper: true,
            wrapperIsBody: false,
            plugins: ['gjs-preset-webpage', 'nonEditablePage', 'customButtons', 'products'],
            pluginsOpts: {
                'gjs-preset-webpage': {
                    exportOpts: false,
                    navbarOpts: false,
                    formsOpts: false,
                    customStyleManager: []
                },
                'customButtons': {
                    textareaId: textareaId
                }
            },
            styleManager: {
                clearProperties: true,
                sectors: []
            }
        });

        editor.Panels.getButton('options', 'sw-visibility').set('active', 1);

        const editableContent = $('#' + textareaId).val();
        editor.getWrapper().find('.gjs-editable')[0].append(editableContent);
    }
}

(new Register()).registerCallback(Grapesjs.init, 'Grapesjs.init');
