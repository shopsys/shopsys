import Register from '../../common/utils/Register';

export default class AdministratorForm {

    constructor ($container) {
        const $roleGroupInput = $container.find('.js-role-group-select');
        const $rolesCustomInputLine = $container.find('.js-role-group-custom').closest('.form-line');

        if ($roleGroupInput.val() !== '') {
            $rolesCustomInputLine.addClass('display-none');
        }

        $roleGroupInput.on('change', function () {
            if ($(this).val() === '') {
                $rolesCustomInputLine.removeClass('display-none');
            } else {
                $rolesCustomInputLine.addClass('display-none');
            }
        });
    }

    static init ($container) {
        $container.filterAllNodes('form[name=administrator_form]').each(function () {
            // eslint-disable-next-line no-new
            new AdministratorForm($(this));
        });
    };
}

(new Register()).registerCallback(AdministratorForm.init, 'AdministratorForm.init');
