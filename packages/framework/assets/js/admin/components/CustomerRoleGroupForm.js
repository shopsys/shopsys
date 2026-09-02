import $ from 'jquery';
import Register from '../../common/utils/Register';

export default class CustomerRoleGroupForm {
    constructor($container) {
        const $allRolesCheckbox = $container.find('[data-scope="all"] .js-roles-permission-checkbox');
        const $individualRolesCheckboxes = $container.find('[data-scope="individual"] .js-roles-permission-checkbox');

        if ($allRolesCheckbox.length === 0) {
            return;
        }

        $allRolesCheckbox.on('change', function () {
            if ($(this).is(':checked')) {
                $individualRolesCheckboxes.prop('checked', false).prop('disabled', true);
            } else {
                $individualRolesCheckboxes.prop('disabled', false);
            }
        });
    }

    static init($container) {
        $container.filterAllNodes('[data-js-customer-role-group]').each(function () {
            // eslint-disable-next-line no-new
            new CustomerRoleGroupForm($(this));
        });
    }
}

new Register().registerCallback(CustomerRoleGroupForm.init, 'CustomerRoleGroupForm.init');
