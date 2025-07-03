import Register from '../../common/utils/Register';

export default class Complaint {
    static init() {
        const $complaintResolutionInput = $('.js-complaint-resolution');
        const $bankAccountNumberInput = $('.js-complaint-bank-account-number');

        $complaintResolutionInput.on('change', function () {
            if ($(this).val() === 'money_return') {
                $bankAccountNumberInput.closest('.form-line').show();
            } else {
                $bankAccountNumberInput.closest('.form-line').hide();
            }
        });

        $complaintResolutionInput.trigger('change');
    }
}

new Register().registerCallback(Complaint.init, 'Complaint.init');
