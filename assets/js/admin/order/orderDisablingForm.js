import Register from 'framework/common/utils/Register';

(new Register()).registerCallback($container => {
    $container.filterAllNodes('#js-order-item-add, #js-order-item-add-product, .js-order-item-remove').remove();
});
