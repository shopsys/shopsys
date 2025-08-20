import Register from '../../common/utils/Register';

export default class Advert {
    constructor($container) {
        this.$positionNameSelect = $container.find('#advert_form_settings_positionName');
        this.$categoryTreeRow = $container.find('[data-js-advert-categories]');
        this.positionNamesWithCategoryTree = ['productListSecondRow'];

        this.$advertTypeSelect = $container.find('input[name="advert_form[settings][type]"]');

        this.initCategoryTreeVisibility();
        this.initContentTypeVisibility($container);
        this.$positionNameSelect.on('change', () => this.initCategoryTreeVisibility());
        this.$advertTypeSelect.on('change', () => this.initContentTypeVisibility($container));
    }

    initContentTypeVisibility($container) {
        const checkedAdvertType = this.$advertTypeSelect.filter(':checked').val();

        $container.find('[data-js-advert-type-content]').hide();
        if (checkedAdvertType) {
            $container.find(`[data-js-advert-type-content=${checkedAdvertType}]`).show();
        }
    }

    initCategoryTreeVisibility() {
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
