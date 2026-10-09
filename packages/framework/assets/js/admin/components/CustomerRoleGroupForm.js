import $ from 'jquery';
import Register from '../../common/utils/Register';

export default class CustomerRoleGroupForm {
    constructor($container) {
        const $allRolesCheckbox = $container.find('[data-scope="all"] .js-roles-permission-checkbox');
        const $individualRolesCheckboxes = $container.find('[data-scope="individual"] .js-roles-permission-checkbox');

        if ($allRolesCheckbox.length === 0 || $individualRolesCheckboxes.length === 0) {
            return;
        }

        const syncIndividualRolesWithAllRoles = () => {
            $individualRolesCheckboxes.prop('checked', $allRolesCheckbox.is(':checked'));
        };

        const uncheckAllRolesWhenIndividualRoleIsUnchecked = event => {
            if (!$(event.target).is(':checked')) {
                $allRolesCheckbox.prop('checked', false);
            }
        };

        $allRolesCheckbox.on('change', syncIndividualRolesWithAllRoles);
        $individualRolesCheckboxes.on('change', uncheckAllRolesWhenIndividualRoleIsUnchecked);

        if ($allRolesCheckbox.is(':checked')) {
            syncIndividualRolesWithAllRoles();
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
