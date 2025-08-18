import Register from '../../common/utils/Register';

export default class Advert {
    constructor($container) {
        this.$positionNameSelect = $container.find('#advert_form_settings_positionName');
        this.$categoryTreeRow = $container.find('[data-js-advert-categories]');
        this.positionNamesWithCategoryTree = ['productListSecondRow'];

        this.initAdvertForm();
        this.$positionNameSelect.on('change', () => this.initAdvertForm());
    }

    initAdvertForm() {
        const checkedPositionName = this.$positionNameSelect.val();

        if (this.positionNamesWithCategoryTree.includes(checkedPositionName)) {
            this.$categoryTreeRow.show();
        } else {
            this.$categoryTreeRow.hide();
        }
    }

    static init($container) {
        const $advertForm = $container.filterAllNodes('form[name="advert_form"]');

        if ($advertForm.length > 0) {
            // eslint-disable-next-line no-new
            new Advert($advertForm);
        }
    }
}

new Register().registerCallback(Advert.init, 'Advert.init');
