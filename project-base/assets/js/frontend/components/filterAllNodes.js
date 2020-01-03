const $ = window.jQuery || global.jQuery || jQuery;

$.fn.filterAllNodes = function (selector) {
    const $result = $(this).find(selector).addBack(selector);

    // .addBack() does not change .prevObject, so we need to do it manually for proper functioning of .end() method
    $result.prevObject = $result.prevObject.prevObject;

    return $result;
};
