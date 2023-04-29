import Register from '../../common/utils/Register';
import { addNewItemToCollection, removeItemFromCollection } from '../validation/customization/customizeCollectionBundle';

export default class MailWhitelist {
    static init () {
        const $mailWhitelistItemAdd = $('.js-mail-whitelist-item-add');
        const $mailWhitelist = $('.js-mail-whitelist');

        $mailWhitelistItemAdd.off('click');
        $mailWhitelist.off('click', '.js-mail-whitelist-item-remove');

        $mailWhitelist.on('click', '.js-mail-whitelist-item-remove', function (event) {
            const $collection = $(this).closest('.js-mail-whitelist');

            const $item = $(this).closest('.js-mail-whitelist-item');
            const index = $item.data('index');
            removeItemFromCollection('.js-mail-whitelist', index);
            $item.remove();

            MailWhitelist.refreshCount($collection);
            event.preventDefault();
        });

        $mailWhitelistItemAdd.on('click', function () {
            const $collection = $('.js-mail-whitelist');
            const index = $collection.data('index');

            const prototype = $collection.data('prototype');
            const item = prototype
                .replace(/__name__label__/g, index)
                .replace(/__name__/g, index);
            const $item = $($.parseHTML(item));
            $item.data('index', index);
            $item.find('input').val('/@example\\.com$/');

            $collection.data('index', index + 1);

            $collection.append($item);
            (new Register()).registerNewContent($item);

            addNewItemToCollection('.js-mail-whitelist', index);
            MailWhitelist.refreshCount($collection);

            return false;
        });

        MailWhitelist.refreshCount($mailWhitelist);
    }

    static refreshCount ($collection) {
        if ($collection.find('.js-mail-whitelist-item').length === 0) {
            $collection.find('.js-mail-whitelist-empty-item').show();
        } else {
            $collection.find('.js-mail-whitelist-empty-item').hide();
        }
    }
}

(new Register()).registerCallback(MailWhitelist.init, 'MailWhitelist.init');
