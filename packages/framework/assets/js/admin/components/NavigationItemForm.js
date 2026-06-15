import Register from '../../common/utils/Register';

const NAVIGATION_ITEM_TYPE_CATEGORIES = 'categories';

export default class NavigationItemForm {
    constructor($form) {
        this.$typeInputs = $form.find(
            '.js-navigation-item-type input[type="radio"], input.js-navigation-item-type[type="radio"]',
        );
        this.$linkFields = $form.find('.js-navigation-item-link-field');
        this.$categoriesFields = $form.find('.js-navigation-item-categories-field');

        this.$typeInputs.on('change', () => this.toggleFields());
        this.toggleFields();
    }

    toggleFields() {
        const selectedType = this.$typeInputs.filter(':checked').val();
        const isTypeCategories = selectedType === NAVIGATION_ITEM_TYPE_CATEGORIES;

        this.$linkFields.toggle(!isTypeCategories);
        this.$categoriesFields.toggle(isTypeCategories);
    }

    static init($container) {
        $container.filterAllNodes('.js-navigation-item-type').each(function () {
            const $form = $(this).closest('form');

            if ($form.length === 0 || $form.data('navigationItemFormInitialized')) {
                return;
            }

            $form.data('navigationItemFormInitialized', true);

            // eslint-disable-next-line no-new
            new NavigationItemForm($form);
        });
    }
}

new Register().registerCallback(NavigationItemForm.init, 'NavigationItemForm.init');
