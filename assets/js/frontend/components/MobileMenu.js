import Register from 'framework/common/utils/Register';

export default class MobileMenu {

    onCategoryExpandControlClick (event) {
        event.stopPropagation();
        event.preventDefault();

        const $categoryExpandControl = $(event.target);
        const $childCategoryList = $($categoryExpandControl.data('child-menu-id'));
        const $categoryItem = $categoryExpandControl.closest('.js-mobile-menu-item');
        const $currentCategoryList = $categoryItem.parent('.js-mobile-menu-list').first();

        $currentCategoryList.animate({ left: '-100%' }, 'fast');
        $currentCategoryList.toggleClass('show-position');
        $childCategoryList.animate({ left: '0px' }, 'fast');
        $childCategoryList.toggleClass('show-position');
    }

    onCategoryCollapseControlClick (event) {
        event.stopPropagation();
        event.preventDefault();

        const $categoryCollapseControl = $(event.target);
        const $parentCategoryList = $($categoryCollapseControl.data('parent-menu-id'));
        const $categoryItem = $categoryCollapseControl.closest('.js-mobile-menu-item');
        const $currentCategoryList = $categoryItem.parent('.js-mobile-menu-list').first();

        $currentCategoryList.animate({ left: '100%' }, 'fast');
        $currentCategoryList.toggleClass('show-position');
        $parentCategoryList.animate({ left: '0px' }, 'fast');
        $parentCategoryList.toggleClass('show-position');
    }

    static init ($container) {
        $container.filterAllNodes('.js-mobile-menu-expand-control')
            .on('click', (event) => (new MobileMenu()).onCategoryExpandControlClick(event));

        $container.filterAllNodes('.js-mobile-menu-collapse-control')
            .on('click', (event) => (new MobileMenu()).onCategoryCollapseControlClick(event));
    }
}

(new Register()).registerCallback(MobileMenu.init, 'MobileMenu.init');
