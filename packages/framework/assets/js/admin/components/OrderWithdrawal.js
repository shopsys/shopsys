import Register from '../../common/utils/Register';

export default class OrderWithdrawal {
    constructor($container) {
        this.$statusSelect = $container.find('[data-js-order-status-select]');
        this.$withdrawalGroup = $container.find('[data-withdrawal-request-exists]');

        if (this.$withdrawalGroup.data('withdrawal-request-exists') === true) {
            return;
        }

        this.handleStatusChange();
        this.$statusSelect.on('change', () => this.handleStatusChange());
    }

    handleStatusChange() {
        const selectedOption = this.$statusSelect.find('option:selected');
        const statusType = selectedOption.data('js-order-status-type');

        if (statusType === 'withdrawn') {
            this.$withdrawalGroup.show();
        } else {
            this.$withdrawalGroup.hide();
        }
    }

    static init($container) {
        const $statusSelect = $container.filterAllNodes('[data-js-order-status-select]');

        if ($statusSelect.length > 0) {
            // eslint-disable-next-line no-new
            new OrderWithdrawal($container);
        }
    }
}

new Register().registerCallback(OrderWithdrawal.init, 'OrderWithdrawal.init');
