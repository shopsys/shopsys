import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import Translator from 'bazinga-translator';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';

/**
 * Lazy-loads the full audit log timeline on the order detail history tab.
 *
 * The initial render only contains a preview of the most recent entries (see
 * OrderController::HISTORY_PREVIEW_LIMIT). The "Show all" button hits a dedicated AJAX
 * endpoint that returns the entire timeline so we don't materialize huge audit logs on
 * every page load.
 */
export default class OrderHistoryLoader {
    static init($container) {
        $container.filterAllNodes('.js-order-history-show-all').on('click', function () {
            const $btn = $(this);
            const loadUrl = $btn.data('load-url');
            const $target = $($btn.data('target'));

            if (!$target.length) {
                return;
            }

            $btn.addClass('btn-loading').prop('disabled', true);

            Ajax.ajax({
                url: loadUrl,
                type: 'GET',
                dataType: 'json',
                success: response => {
                    if (response?.html) {
                        $target.html(response.html);
                        new Register().registerNewContent($target);
                        $btn.remove();

                        return;
                    }

                    OrderHistoryLoader.showError($btn);
                },
                error: () => {
                    OrderHistoryLoader.showError($btn);
                },
            });
        });
    }

    static showError($btn) {
        // eslint-disable-next-line no-new
        new ModalWindow({
            content: Translator.trans('Error occurred, try again please.'),
        });
        $btn.removeClass('btn-loading').prop('disabled', false);
    }
}

new Register().registerCallback(OrderHistoryLoader.init, 'OrderHistoryLoader.init');
