import Register from '../../common/utils/Register';

export default class SelectAdminDomains {

    constructor ($selectDomainsDropdown) {
        this.$selectDomainsDropdown = $selectDomainsDropdown;
        this.$selectDomainsDropdown.find('.js-domains-select-all').on('click', () => this.selectAll());
        this.$selectDomainsDropdown.find('.js-domains-select-none').on('click', () => this.selectNone());
    }

    static init () {
        $('.js-domains-select').each(function () {
            // eslint-disable-next-line no-new
            new SelectAdminDomains($(this));
        });
    }

    selectAll () {
        this.$selectDomainsDropdown.find('input[type=checkbox]').prop('checked', true);
    }

    selectNone () {
        this.$selectDomainsDropdown.find('input[type=checkbox]').prop('checked', false);
    }
}

(new Register()).registerCallback(SelectAdminDomains.init, 'SelectAdminDomains.init');
