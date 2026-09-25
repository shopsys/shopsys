import Register from '../../common/utils/Register';

export default class CustomerRoleGroupForm {
    constructor($container) {
        const $allRolesCheckbox = $container.find('[data-scope="all"] .js-roles-permission-checkbox');
        const $individualRolesCheckboxes = $container.find('[data-scope="individual"] .js-roles-permission-checkbox');

        if ($allRolesCheckbox.length === 0) {
            return;
        }

        const applyAllRolesState = function () {
            if ($allRolesCheckbox.is(':checked')) {
                $individualRolesCheckboxes.prop('checked', true).prop('disabled', true);
            } else {
                $individualRolesCheckboxes.prop('checked', false).prop('disabled', false);
            }
        };

        $allRolesCheckbox.on('change', applyAllRolesState);

        // the "all" scope grants every individual permission, so show them as checked also on page load
        if ($allRolesCheckbox.is(':checked')) {
            applyAllRolesState();
        }
    }

    static init($container) {
        $container.filterAllNodes('[data-js-customer-role-group]').each(function () {
            // eslint-disable-next-line no-new
            new CustomerRoleGroupForm($(this));
        });
    }
}

new Register().registerCallback(CustomerRoleGroupForm.init, 'CustomerRoleGroupForm.init');
