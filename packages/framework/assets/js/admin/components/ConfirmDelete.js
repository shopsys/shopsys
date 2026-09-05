import $ from 'jquery';
import Ajax from '../../common/utils/Ajax';

export default class ConfirmDelete {
    constructor(confirmLink, messageContainerSelector = '.modal.show .modal-body') {
        this.confirmLink = confirmLink;
        this.messageContainerSelector = messageContainerSelector;
        this.$confirmLink = $(confirmLink);
        this.$messageContainer = $(messageContainerSelector);
        this.$confirmDeleteForm = this.$messageContainer.find('.js-confirm-delete-form');
        this.$confirmDeleteFormSelect = this.$confirmDeleteForm.find('.js-confirm-delete-select');
        this.$confirmDeleteFormButton = this.$confirmDeleteForm.find('.btn');
        this.$directDeleteLink = this.$messageContainer.find('.js-confirm-delete-direct-link');

        if (this.$directDeleteLink.length !== 0) {
            this.$directDeleteLink.click(() => this.canDeleteDirectly());
        } else {
            this.$confirmDeleteForm.submit(() => this.onConfirmDeleteFormSubmit());
            this.$confirmDeleteFormSelect.change(() => this.refreshSubmitButton());
            this.refreshSubmitButton();
        }
    }

    canDeleteDirectly() {
        const _this = this;
        Ajax.ajax({
            url: this.$confirmLink.attr('href'),
            success: function (data) {
                if ($($.parseHTML(data)).find('.js-confirm-delete-direct-link').length > 0) {
                    document.location = _this.$directDeleteLink.attr('href');
                } else {
                    this.$messageContainer.html(data);
                    // eslint-disable-next-line no-new
                    new ConfirmDelete(_this.confirmLink, _this.messageContainerSelector);
                }
            },
        });

        return false;
    }

    refreshSubmitButton() {
        if (this.isSelectedNewValue()) {
            this.$confirmDeleteFormButton.removeClass('disabled cursor-help');
        } else {
            this.$confirmDeleteFormButton.addClass('disabled cursor-help');
        }
    }

    onConfirmDeleteFormSubmit() {
        return this.isSelectedNewValue();
    }

    isSelectedNewValue() {
        return this.$confirmDeleteFormSelect.val() !== '';
    }
}
