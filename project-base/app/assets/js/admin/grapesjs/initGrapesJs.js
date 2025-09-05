import { Buffer } from 'buffer';
import Register from 'framework/common/utils/Register';
import GrapesMailEditor from './GrapesMailEditor';
import GrapesWebEditor from './GrapesWebEditor';
import './grapesjs-non-editable-page';
import './plugins/mail/image-with-variable';
import './plugins/mail/mail-button-link';
import './plugins/mail/mail-image';
import './plugins/mail/template';
import './plugins/mail/text';
import './plugins/shared/buttons';
import './plugins/shared/ckeditor.js';
import './plugins/web/column1';
import './plugins/web/column2';
import './plugins/web/column3';
import './plugins/web/iframe';
import './plugins/web/image-file';
import './plugins/web/link.js';
import './plugins/web/map';
import './plugins/web/products';
import './plugins/web/table';
import './plugins/web/text';
import './plugins/web/text-with-image';
import './plugins/web/video';
import './plugins/web/web-button-link';
import './plugins/web/web-image';

global.Buffer = Buffer;

export default class InitGrapesJs {
    static init($container) {
        let isAnyButtonOnPage = false;
        $container.filterAllNodes('.js-grapesjs-button').each((_index, element) => {
            $(element).on('click', event => {
                const frontendUrl = $(element).data('template-url');
                const textareaId = $(element).data('textarea-id');
                const elfinderUrl = $(element).data('elfinder-url');
                const allowProducts = $(element).data('allow-products');

                GrapesWebEditor.openGrapesEditor(event, frontendUrl, textareaId, elfinderUrl, allowProducts);
            });

            isAnyButtonOnPage = true;
        });

        $container.filterAllNodes('.js-grapesjs-mail-button').each((_index, element) => {
            $(element).on('click', event => {
                const textareaId = $(element).data('textarea-id');
                const elfinderUrl = $(element).data('elfinder-url');
                const templateHtml = $(element).data('template');
                const bodyVariables = $(element).data('variables');
                const customPlugins = $(element).data('custom-plugins');

                GrapesMailEditor.openGrapesMailEditor(
                    event,
                    textareaId,
                    elfinderUrl,
                    templateHtml,
                    bodyVariables,
                    customPlugins,
                );
            });

            isAnyButtonOnPage = true;
        });

        if (isAnyButtonOnPage === true) {
            $('body').append('<div id="grapesjs"></div>');
        }
    }
}

new Register().registerCallback(InitGrapesJs.init, 'InitGrapesJs.init');
