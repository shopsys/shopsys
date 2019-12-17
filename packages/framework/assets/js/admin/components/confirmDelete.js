import Ajax from '../../common/ajax';

export default class ConfirmDelete {

    constructor (confirmLink, messageContainerSelector = '#window-main-container .window .js-window-content') {
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

    canDeleteDirectly () {
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
            }
        });

        return false;
    }

    refreshSubmitButton () {
        if (this.isSelectedNewValue()) {
            this.$confirmDeleteFormButton
                .removeClass('btn--disabled cursor-help')
                .tooltip('destroy');
        } else {
            this.$confirmDeleteFormButton
                .addClass('btn--disabled cursor-help')
                .tooltip({
                    // title: Shopsys.translator.trans('Choose new value first'),
                    title: 'Choose new value first',
                    placement: 'right'
                });
        }
    }

    onConfirmDeleteFormSubmit () {
        return this.isSelectedNewValue();
    }

    isSelectedNewValue () {
        return this.$confirmDeleteFormSelect.val() !== '';
    }
}
