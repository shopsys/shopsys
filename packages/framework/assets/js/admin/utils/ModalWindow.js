import Translator from 'bazinga-translator';
import Warning from 'icons/tabler/alert-triangle.svg';

const defaults = {
    content: '',
    buttonClose: true,
    buttonCancel: false,
    buttonContinue: false,
    urlContinue: '#',
    wide: false,
    cssClass: '',
    closeOnBgClick: true,
    modalStatus: null,
    eventClose: function () {},
    eventContinue: function () {},
    eventCancel: function () {},
};

const mainTemplate = `<div class="modal modal-blur fade" id="window-modal" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
            </div> 
        </div> 
    </div> 
</div>`;
const closeButtonTemplate =
    '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="' +
    Translator.trans('Close') +
    '"></button>';
const modalStatusTemplate = '<div class="modal-status"></div>';
const modalFooterTemplate = `<div class="modal-footer">
    <div class="w-100">
        <div class="row">
        </div>
    </div>
</div>`;
const footerButtonTemplate = `<div class="col">
    <a href="#" class="btn w-100"></a>
</div>`;

export default class ModalWindow {
    /**
     * content (string)
     * buttonClose (bool)
     * buttonContinue (bool)
     * textContinue (string)
     * eventClose (function)
     * eventContinue (function)
     * urlContinue (string)
     * status (string|null)
     */
    constructor(inputOptions) {
        const options = {
            textContinue: Translator.trans('Yes'),
            textCancel: Translator.trans('No'),
            ...defaults,
            ...inputOptions,
        };

        const $modal = $(mainTemplate);
        const $modalDialog = $modal.find('.modal-dialog');
        const $modalBody = $modal.find('.modal-body');
        const $modalContent = $modal.find('.modal-content');

        $modalDialog.addClass(options.wide ? 'modal-xl' : 'modal-sm');

        if (options.modalStatus) {
            const $modalStatus = $(modalStatusTemplate);
            $modalStatus.addClass('bg-' + options.modalStatus);
            $modalContent.prepend($modalStatus);

            if (options.modalStatus === 'danger') {
                const icon = $(Warning);
                icon.addClass('icon icon-lg mb-2 text-danger');
                $modalBody.append(icon);
            }
        }

        if (options.buttonClose) {
            $modalContent.prepend(closeButtonTemplate);
        }

        $modalBody.append(options.content);

        if (options.buttonCancel || options.buttonContinue) {
            const $modalFooter = $(modalFooterTemplate);

            if (options.buttonCancel) {
                const $buttonCancel = $(footerButtonTemplate);
                const $buttonCancelLink = $buttonCancel.find('a');

                $buttonCancelLink.text(options.textCancel);
                $buttonCancelLink.attr('data-bs-dismiss', 'modal');

                $modalFooter.find('.row').append($buttonCancel);
            }

            if (options.buttonContinue) {
                const $buttonContinue = $(footerButtonTemplate);
                const $buttonContinueLink = $buttonContinue.find('a');

                $buttonContinueLink.text(options.textContinue);
                $buttonContinueLink.attr('href', options.urlContinue);
                $buttonContinueLink.addClass('btn-' + (options.modalStatus ?? 'primary'));

                $modalFooter.find('.row').append($buttonContinue);
            }

            $modalContent.append($modalFooter);
        }

        $('body').append($modal);

        $modal.modal({ backdrop: options.closeOnBgClick ? true : 'static' });
        $modal.modal('show');

        $modal.on('hidden.bs.modal', () => {
            $modal.remove();
        });
    }
}
