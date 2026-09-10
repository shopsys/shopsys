export default class ConfirmDelete {
    constructor(messageContainerSelector = '.modal.show .modal-body') {
        this.$messageContainer = $(messageContainerSelector);
        this.$confirmDeleteForm = this.$messageContainer.find('.js-confirm-delete-form');
        this.$confirmDeleteFormSelect = this.$confirmDeleteForm.find('.js-confirm-delete-select');
        this.$confirmDeleteFormButton = this.$confirmDeleteForm.find('.btn');

        this.$confirmDeleteForm.submit(() => this.onConfirmDeleteFormSubmit());
        this.$confirmDeleteFormSelect.change(() => this.refreshSubmitButton());
        this.refreshSubmitButton();
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
