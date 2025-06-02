import Register from '../../common/utils/Register';

export default class AdministratorForm {

    constructor ($container) {
        const $roleGroupInput = $container.find('.js-role-group-select');
        const $rolesCustomInputLine = $container.find('.js-role-group-custom').closest('div.row');

        console.log($container.find('.js-role-group-custom'));

        if ($roleGroupInput.val() !== '') {
            $rolesCustomInputLine.addClass('d-none');
        }

        $roleGroupInput.on('change', function () {
            if ($(this).val() === '') {
                $rolesCustomInputLine.removeClass('d-none');
            } else {
                $rolesCustomInputLine.addClass('d-none');
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
