import FormChangeInfo from './FormChangeInfo';
import Register from '../../common/utils/Register';

export default class EntityUrlsRow {

    constructor ($row) {
        this.$checkbox = $row.find('.js-entity-url-list-row-checkbox');
        this.$deleteBlock = $row.find('.js-entity-url-list-row-delete-block');
        this.$deleteBlockButton = this.$deleteBlock.find('.js-entity-url-list-row-delete-block-button');
        this.$revertBlock = $row.find('.js-entity-url-list-row-revert-block');
        this.$revertBlockButton = this.$revertBlock.find('.js-entity-url-list-row-revert-block-button');
        this.$radio = $row.find('.js-entity-url-list-select-main');
        this.$deleteRevertWrapper = $row.find('.js-entity-url-list-row-delete-revert-wrapper');

        const _this = this;
        _this.$deleteBlockButton.click(function () {
            _this.markAsDeleted(true);
            FormChangeInfo.showInfo();
            return false;
        });

        _this.$revertBlockButton.click(function () {
            _this.markAsDeleted(false);
            FormChangeInfo.showInfo();
            return false;
        });

        _this.$radio.change(function () {
            const $allRadioButtons = _this.$radio.closest('table').find('.js-entity-url-list-select-main');
            $allRadioButtons.each(function () {
                _this.updateMain($(this));
            });
        });
    }

    markAsDeleted (toDelete) {
        this.$checkbox.prop('checked', toDelete);
        this.$radio.attr('disabled', toDelete);
        this.$deleteBlock.toggleClass('d-none', toDelete);
        this.$revertBlock.toggleClass('d-none', !toDelete);
    }

    markAsMain (isMain) {
        this.$deleteRevertWrapper.toggleClass('d-none', !isMain);
    }

    updateMain (radio) {
        const $row = radio.closest('.js-entity-url-list-friendly-url');
        const isMain = radio.is(':checked');
        const $deleteRevertWrapper = $row.find('.js-entity-url-list-row-delete-revert-wrapper');
        $deleteRevertWrapper.toggleClass('d-none', !isMain);
    }

    static init ($container) {
        $container.filterAllNodes('.js-entity-url-list-friendly-url').each(function () {
            void new EntityUrlsRow($(this));
        });
    }
}

(new Register()).registerCallback(EntityUrlsRow.init, 'EntityUrlsRow.init');
