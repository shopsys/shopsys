import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';
import ConfirmDelete from './ConfirmDelete';
import ModalWindow from '../utils/ModalWindow';
import Translator from 'bazinga-translator';

export default class AjaxConfirm {

    static bind () {
        const _this = this;
        $(this)
            .off('click.ajaxConfirm')
            .on('click.ajaxConfirm', function () {
                Ajax.ajax({
                    url: $(this).attr('href'),
                    context: this,
                    success: function (data) {
                        const content
                            = '<h3>'
                                + Translator.trans('Are you sure?')
                            + '</h3>'
                            + '<div class="text-secondary">'
                                + data
                            + '</div>';

                        void new ModalWindow({
                            content: content,
                            modalStatus: 'danger'
                        });
                        const onOpen = $(_this).data('ajax-confirm-on-open');
                        if (onOpen) {
                            void new ConfirmDelete(this);
                        }
                    }
                });

                return false;
            });
    }

    static init ($container) {
        $container.filterAllNodes('a.js-ajax-confirm').each(AjaxConfirm.bind);
    }
}

(new Register()).registerCallback(AjaxConfirm.init, 'AjaxConfirm.init');
