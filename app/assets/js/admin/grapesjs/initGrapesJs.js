import Register from 'framework/common/utils/Register';
import grapesjs from 'grapesjs';
import 'grapesjs-preset-webpage';
import './grapesjs-custom-buttons-plugin';

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

        const content = $('#' + textareaId).val();

        grapesjs.init({
            container: '#grapesjs',
            components: content,
            height: '100%',
            width: '100%',
            storageManager: false,
            noticeOnUnload: false,
            plugins: ['gjs-preset-webpage', 'customButtons'],
            pluginsOpts: {
                'gjs-preset-webpage': {
                    exportOpts: false,
                    navbarOpts: false
                },
                'customButtons': {
                    textareaId: textareaId
                }
            },
            styleManager: {
                clearProperties: true
            }
        });
    }
}

(new Register()).registerCallback(Grapesjs.init, 'Grapesjs.init');
