import 'jquery-hoverintent';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';
import Window from '../utils/Window';

export default class ProductVisibility {
    constructor($productVisibility) {
        this.$visibilityIcon = $productVisibility.find('.js-product-visibility-icon');
        this.$visibilityBox = $productVisibility.find('.js-product-visibility-box');
        this.$visibilityBoxWindow = this.$visibilityBox.find('.js-product-visibility-box-window');

        this.url = $productVisibility.data('visibility-url');
        this.isLoading = false;
        this.isLoaded = false;
        this.showInWindowAfterLoad = false;

        let keepVisible = false;
        this.$visibilityIcon
            .mouseleave(() => {
                keepVisible = false;
                setTimeout(() => {
                    if (!keepVisible) {
                        this.$visibilityBox.hide();
                    }
                }, 20); // Mouse needs some time to leave the icon and enter the $visibilityBox
            })
            .click(() => {
                if (this.isLoaded) {
                    ProductVisibility.showInWindow(this);
                } else {
                    this.showInWindowAfterLoad = true;
                }
            })
            .hoverIntent({
                interval: 200,
                over: () => {
                    this.$visibilityBox.show();
                    if (!this.isLoaded && !this.isLoading) {
                        this.isLoading = true;
                        Ajax.ajax({
                            loaderElement: this.$visibilityIcon,
                            url: this.url,
                            success: response => ProductVisibility.onLoadVisibility(response, this),
                        });
                    }
                },
                out: () => {},
            });
        this.$visibilityBox
            .mouseenter(() => {
                keepVisible = true;
            })
            .mouseleave(() => {
                this.$visibilityBox.hide();
            });
    }

    static showInWindow(productVisibility) {
        // eslint-disable-next-line no-new
        new Window({
            content: productVisibility.$visibilityBoxWindow.html(),
        });
    }

    static onLoadVisibility(responseHtml, productVisibility) {
        productVisibility.isLoading = false;
        productVisibility.isLoaded = true;
        productVisibility.$visibilityBoxWindow.html(responseHtml);
        productVisibility.$visibilityBoxWindow.show();
        if (productVisibility.showInWindowAfterLoad) {
            ProductVisibility.showInWindow(productVisibility);
        }
    }

    static init($container) {
        $container.filterAllNodes('.js-product-visibility').each(function () {
            // eslint-disable-next-line no-new
            new ProductVisibility($(this));
        });
    }
}

new Register().registerCallback(ProductVisibility.init, 'ProductVisibility.init');
