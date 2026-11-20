import Register from '../../common/utils/Register';

export default class Complaint {
    constructor($container) {
        this.$complaintPreferredResolutionInput = $container.find('.js-complaint-preferred-resolution');
        this.$bankAccountNumberRow = $container.find('[data-js-complaint-bank-account-number]');

        this.$complaintPreferredResolutionInput.on('change', () => this.handlePreferredResolutionChange());
        this.handlePreferredResolutionChange();
    }

    handlePreferredResolutionChange() {
        if (this.$complaintPreferredResolutionInput.val() === 'money_return') {
            this.$bankAccountNumberRow.show();
        } else {
            this.$bankAccountNumberRow.hide();
        }
    }

    static init($container) {
        const $complaintForm = $container.filterAllNodes('.js-complaint-preferred-resolution');

        if ($complaintForm.length > 0) {
            // eslint-disable-next-line no-new
            new Complaint($container);
        }
    }
}

new Register().registerCallback(Complaint.init, 'Complaint.init');
