import $ from 'jquery';
import Register from '../../common/utils/Register';

export default class AdministratorForm {
    constructor($container) {
        const $roleGroupInput = $container.find('.js-role-group-select');
        const $rolesCustomInputRow = $container.find('[data-js-role-group-custom]');

        if ($roleGroupInput.length === 0) {
            return;
        }

        if ($roleGroupInput.val() !== '') {
            $rolesCustomInputRow.hide();
        }

        $roleGroupInput.on('change', function () {
            if ($(this).val() === '') {
                $rolesCustomInputRow.show();
            } else {
                $rolesCustomInputRow.hide();
            }
        });
    }

    static init($container) {
        $container.filterAllNodes('form[name=administrator_form]').each(function () {
            // eslint-disable-next-line no-new
            new AdministratorForm($(this));
        });
    }
}

new Register().registerCallback(AdministratorForm.init, 'AdministratorForm.init');
