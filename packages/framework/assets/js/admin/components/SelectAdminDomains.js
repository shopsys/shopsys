import Register from '../../common/utils/Register';

export default class SelectAdminDomains {
    constructor($selectDomainsDropdown) {
        this.$selectDomainsDropdown = $selectDomainsDropdown;
        this.$selectDomainsDropdown
            .find('input[type=checkbox]:not(.js-domains-select-all-checkbox)')
            .on('change', () => this.updateIndeterminate());
        this.$selectDomainsDropdown.find('.js-domains-select-all-checkbox').on('click', () => this.checkboxChange());
        this.updateIndeterminate();
    }

    static init() {
        $('.js-domains-select').each(function () {
            // eslint-disable-next-line no-new
            new SelectAdminDomains($(this));
        });
    }

    updateIndeterminate() {
        let allChecked = true;
        let allUnchecked = true;
        this.$selectDomainsDropdown
            .find('input[type=checkbox]:not(.js-domains-select-all-checkbox)')
            .each((_, element) => {
                if ($(element).prop('checked')) {
                    allUnchecked = false;
                } else if (!$(element).prop('checked')) {
                    allChecked = false;
                }
            });

        if (allChecked || allUnchecked) {
            this.$selectDomainsDropdown
                .find('.js-domains-select-all-checkbox')
                .prop('indeterminate', false)
                .prop('checked', allChecked);
            return;
        }

        this.$selectDomainsDropdown.find('.js-domains-select-all-checkbox').prop('indeterminate', true);
    }

    selectAll() {
        this.$selectDomainsDropdown
            .find('input[type=checkbox]:not(.js-domains-select-all-checkbox)')
            .prop('checked', true);
    }

    selectNone() {
        this.$selectDomainsDropdown
            .find('input[type=checkbox]:not(.js-domains-select-all-checkbox)')
            .prop('checked', false);
    }

    checkboxChange() {
        if (!this.$selectDomainsDropdown.find('.js-domains-select-all-checkbox').prop('checked')) {
            this.selectNone();
            return;
        }

        this.selectAll();
    }
}

new Register().registerCallback(SelectAdminDomains.init, 'SelectAdminDomains.init');
