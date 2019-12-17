import Register from '../../common/register';

export default class GridMassAction {

    constructor ($grid) {
        this.$grid = $grid;
        this.$selectAllCheckbox = $grid.find('.js-grid-mass-action-select-all');
        this.$selectAllCheckbox.click(() => this.onSelectAll());
    }

    onSelectAll () {
        this.$grid.find('.js-grid-mass-action-select-row').prop('checked', this.$selectAllCheckbox.is(':checked'));
    }

    static init () {
        $('.js-grid').each(function () {
            // eslint-disable-next-line no-new
            new GridMassAction($(this));
        });
    }
}

(new Register()).registerCallback(GridMassAction.init);
