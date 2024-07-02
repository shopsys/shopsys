import Register from '../../common/utils/Register';

export default class RoleGroups {

    constructor ($container) {
        const _this = this;
        this.$container = $container;
        this.COLUMN_FULL_SELECTOR = 'js-roles-column-full';
        this.COLUMN_VIEW_SELECTOR = 'js-roles-column-view';
        this.$allFullCheckbox = $container.find('.js-roles-first-row').find('.js-roles-column-full input[type=checkbox]');
        this.$allViewCheckbox = $container.find('.js-roles-first-row').find('.js-roles-column-view input[type=checkbox]');

        this.$allFullCheckbox.on('change', function () {
            _this.changeAllCheckbox(this, _this.COLUMN_FULL_SELECTOR);
        });
        this.$allViewCheckbox.on('change', function () {
            _this.changeAllCheckbox(this, _this.COLUMN_VIEW_SELECTOR);
        });

        // Remove checked for view checkbox if full checkbox is checked and vise versa
        $container.find('.js-roles-row:not(.js-roles-first-row)').filterAllNodes('input[type=checkbox]').on('change', function () {
            const $row = $(this).closest('.js-roles-row');
            const fullCheckboxClicked = $(this).closest('.js-roles-column').hasClass(_this.COLUMN_FULL_SELECTOR);
            const $fullColumn = $row.find('.js-roles-column-full');
            const $viewColumn = $row.find('.js-roles-column-view');

            if (this.checked) {
                if (fullCheckboxClicked === true) {
                    $viewColumn.find('input[type=checkbox]').prop('checked', false).trigger('change');
                    _this.$allViewCheckbox.prop('checked', false);
                }

                if (fullCheckboxClicked === false) {
                    $fullColumn.find('input[type=checkbox]').prop('checked', false).trigger('change');
                    _this.$allFullCheckbox.prop('checked', false);
                }
            } else {
                if (fullCheckboxClicked === true) { _this.$allFullCheckbox.prop('checked', false); }
                if (fullCheckboxClicked === false) { _this.$allViewCheckbox.prop('checked', false); }
            }
        });
    }

    static init () {
        const $container = $('#administrator_role_group_form_roles');
        // eslint-disable-next-line no-new
        new RoleGroups($container);
    };

    // Set all checkboxes in column as selected or deselected if `all` checkbox is checked
    changeAllCheckbox ($input, checkboxesSelector) {
        if ($input.checked) {
            this.$container.filterAllNodes('.js-roles-row:not(.js-roles-first-row) .' + checkboxesSelector + ' input[type=checkbox]').prop('checked', true).trigger('change');
        } else {
            this.$container.filterAllNodes('.js-roles-row:not(.js-roles-first-row) .' + checkboxesSelector + ' input[type=checkbox]').prop('checked', false).trigger('change');
        }
    };
}

(new Register()).registerCallback(RoleGroups.init, 'RoleGroups.init');
