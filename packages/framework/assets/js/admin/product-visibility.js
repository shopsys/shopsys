import 'jquery-hoverintent';
import Ajax from '../common/ajax';
import Window from './window';
import Register from '../common/register';

export default class ProductVisibility {

    constructor ($productVisibility) {
        this.$visibilityIcon = $productVisibility.find('.js-product-visibility-icon');
        this.$visibilityBox = $productVisibility.find('.js-product-visibility-box');
        this.$visibilityBoxWindow = this.$visibilityBox.find('.js-product-visibility-box-window');

        this.url = $productVisibility.data('visibility-url');
        this.isLoading = false;
        this.isLoaded = false;
        this.showInWindowAfterLoad = false;

        let keepVisible = false;
        const _this = this;
        this.$visibilityIcon
            .mouseleave(function () {
                keepVisible = false;
                setTimeout(function () {
                    if (!keepVisible) {
                        _this.$visibilityBox.hide();
                    }
                }, 20); // Mouse needs some time to leave the icon and enter the $visibilityBox
            })
            .click(function () {
                if (_this.isLoaded) {
                    ProductVisibility.showInWindow(_this);
                } else {
                    _this.showInWindowAfterLoad = true;
                }
            })
            .hoverIntent({
                interval: 200,
                over: function () {
                    _this.$visibilityBox.show();
                    if (!_this.isLoaded && !_this.isLoading) {
                        _this.isLoading = true;
                        Ajax.ajax({
                            loaderElement: _this.$visibilityIcon,
                            url: _this.url,
                            success: (response) => ProductVisibility.onLoadVisibility(response, _this)
                        });
                    }
                },
                out: function () {}
            });
        this.$visibilityBox
            .mouseenter(function () {
                keepVisible = true;
            })
            .mouseleave(function () {
                _this.$visibilityBox.hide();
            });
    }

    static showInWindow (productVisibility) {
        // eslint-disable-next-line no-new
        new Window({
            content: productVisibility.$visibilityBoxWindow.html()
        });
    };

    static onLoadVisibility (responseHtml, productVisibility) {
        productVisibility.isLoading = false;
        productVisibility.isLoaded = true;
        productVisibility.$visibilityBoxWindow.html(responseHtml);
        productVisibility.$visibilityBoxWindow.show();
        if (productVisibility.showInWindowAfterLoad) {
            ProductVisibility.showInWindow(productVisibility);
        }
    };

    static init () {
        $('.js-product-visibility').each(function () {
            // eslint-disable-next-line no-new
            new ProductVisibility($(this));
        });
    }
}

(new Register()).registerCallback(ProductVisibility.init);
