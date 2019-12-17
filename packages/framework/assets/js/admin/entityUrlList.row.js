import FormChangeInfo from './formChangeInfo';
import Register from '../common/register';

export default class EntityUrlsRow {

    constructor ($row) {
        this.$label = $row.find('.js-entity-url-list-row-label');
        this.$checkbox = $row.find('.js-entity-url-list-row-checkbox');
        this.$deleteBlock = $row.find('.js-entity-url-list-row-delete-block');
        this.$deleteBlockButton = this.$deleteBlock.find('.js-entity-url-list-row-delete-block-button');
        this.$revertBlock = $row.find('.js-entity-url-list-row-revert-block');
        this.$revertBlockButton = this.$revertBlock.find('.js-entity-url-list-row-revert-block-button');
        this.$radio = $row.find('.js-entity-url-list-select-main');
        this.$mainDeleteInfo = $row.find('.js-entity-url-list-info-main-delete');
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

        _this.markAsDeleted(_this.$checkbox.is(':checked'));
        _this.markAsMain(_this.$radio.is(':checked'));
    }

    markAsDeleted (toDelete) {
        this.$checkbox.prop('checked', toDelete);
        this.$radio.attr('disabled', toDelete);
        this.$label.toggleClass('text-disabled', toDelete);
        this.$deleteBlock.toggle(!toDelete);
        this.$revertBlock.toggle(toDelete);
    }

    markAsMain (isMain) {
        this.$deleteRevertWrapper.toggle(!isMain);
        this.$mainDeleteInfo.toggle(isMain);
    }

    updateMain (radio) {
        const $row = radio.closest('.js-entity-url-list-friendly-url');
        const isMain = radio.is(':checked');
        const $mainDeleteInfo = $row.find('.js-entity-url-list-info-main-delete');
        const $deleteRevertWrapper = $row.find('.js-entity-url-list-row-delete-revert-wrapper');
        $deleteRevertWrapper.toggle(!isMain);
        $mainDeleteInfo.toggle(isMain);
    }

    static init ($container) {
        $container.filterAllNodes('.js-entity-url-list-friendly-url').each(function () {
            // eslint-disable-next-line no-new
            new EntityUrlsRow($(this));
        });
    }
}

(new Register()).registerCallback(EntityUrlsRow.init);
