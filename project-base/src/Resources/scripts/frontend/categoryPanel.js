(function ($) {

    Shopsys = window.Shopsys || {};
    Shopsys.categoryPanel = Shopsys.categoryPanel || {};

    Shopsys.categoryPanel.init = function ($container) {
        $container.filterAllNodes('.js-category-collapse-control')
            .on('click', onCategoryCollapseControlClick);
    };

    function onCategoryCollapseControlClick (event) {
        event.stopPropagation();
        event.preventDefault();

        var $categoryCollapseControl = $(this);
        var $categoryItem = $categoryCollapseControl.closest('.js-category-item');
        var $categoryList = $categoryItem.find('.js-category-list').first();
        var isOpen = $categoryCollapseControl.hasClass('open');

        if (!$categoryItem.hasClass("intented")) {

            if (isOpen) {
                $categoryList.slideUp('fast');
                $categoryItem.removeClass('active');
                $categoryCollapseControl.removeClass('open');
            } else if ($categoryList.length > 0) {
                $categoryList.slideDown('fast');
                $categoryItem.addClass('active');
                $categoryCollapseControl.addClass('open');
            } else {
                loadCategoryItemContent($categoryItem, $categoryCollapseControl.data('url'));
                $categoryItem.addClass('active');
                $categoryCollapseControl.addClass('open');
            }
        }
    }

    function loadCategoryItemContent ($categoryItem, url) {
        Shopsys.ajax({
            loaderElement: $categoryItem.find('.js-category-list-placeholder'),
            url: url,
            dataType: 'html',
            success: function (data) {
                var $categoryListPlaceholder = $categoryItem.find('.js-category-list-placeholder');
                var $categoryList = $($.parseHTML(data));

                $categoryListPlaceholder.replaceWith($categoryList);
                $categoryList.hide().slideDown('fast');

                Shopsys.register.registerNewContent($categoryList);
            }
        });
    }

    Shopsys.register.registerCallback(Shopsys.categoryPanel.init);

})(jQuery);
