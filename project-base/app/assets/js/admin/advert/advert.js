import Register from 'framework/common/utils/Register';

(new Register()).registerCallback($container => {
    const getCheckedPositionName = function () {
        return $('#advert_form_settings_positionName').val();
    };

    const initAdvertForm = function () {
        const positionNamesWithCategoryTree = [
            'productListSecondRow'
        ];

        if (positionNamesWithCategoryTree.includes(getCheckedPositionName())) {
            $('#advert_form_settings').find('.js-category-tree-form').closest('.form-line').show();
        } else {
            $('#advert_form_settings').find('.js-category-tree-form').closest('.form-line').hide();
        }
    };

    initAdvertForm();
    $('#advert_form_settings_positionName').change(initAdvertForm);
});
