import Register from '../../common/utils/Register';

export default class Complaint {
    constructor($container) {
        this.$complaintResolutionInput = $container.find('.js-complaint-resolution');
        this.$bankAccountNumberRow = $container.find('[data-js-complaint-bank-account-number]');

        this.$complaintResolutionInput.on('change', () => this.handleResolutionChange());
        this.$complaintResolutionInput.trigger('change');
    }

    handleResolutionChange() {
        if (this.$complaintResolutionInput.val() === 'money_return') {
            this.$bankAccountNumberRow.show();
        } else {
            this.$bankAccountNumberRow.hide();
        }
    }

    static init($container) {
        const $complaintForm = $container.filterAllNodes('.js-complaint-resolution');

        if ($complaintForm.length > 0) {
            // eslint-disable-next-line no-new
            new Complaint($container);
        }
    }
}

new Register().registerCallback(Complaint.init, 'Complaint.init');
