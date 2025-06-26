import Register from '../../common/utils/Register';

new Register().registerCallback(_$container => {
    const getCheckedPositionName = () => $('#advert_form_settings_positionName').val();

    const initAdvertForm = () => {
        const positionNamesWithCategoryTree = ['productListSecondRow'];

        if (positionNamesWithCategoryTree.includes(getCheckedPositionName())) {
            $('#advert_form_settings').find('.js-category-tree-form').closest('.form-line').show();
        } else {
            $('#advert_form_settings').find('.js-category-tree-form').closest('.form-line').hide();
        }
    };

    initAdvertForm();
    $('#advert_form_settings_positionName').change(initAdvertForm);
});
