import Ajax from '../common/ajax';
import Window from './window';
import Register from '../common/register';

export default class OrderPreview {

    constructor ($orderPreview) {
        this.$previewIcon = $orderPreview.find('.js-order-preview-icon');
        this.$previewBox = $orderPreview.find('.js-order-preview-box');
        this.$previewBoxWindow = this.$previewBox.find('.js-order-preview-box-window');

        this.url = $orderPreview.data('preview-url');
        this.isLoading = false;
        this.isLoaded = false;
        this.showInWindowAfterLoad = false;

        let keepVisible = false;

        const _this = this;
        this.$previewIcon
            .mouseleave(function () {
                keepVisible = false;
                setTimeout(function () {
                    if (!keepVisible) {
                        _this.$previewBox.hide();
                    }
                }, 20); // Mouse needs some time to leave the icon and enter the $visibilityBox
            })
            .click(function () {
                if (isLoaded) {
                    _this.showInWindow();
                } else {
                    _this.showInWindowAfterLoad = true;
                }
            })
            .hoverIntent({
                interval: 200,
                over: function () {
                    _this.$previewBox.show();
                    if (!_this.isLoaded && !_this.isLoading) {
                        _this.isLoading = true;
                        Ajax.ajax({
                            loaderElement: null,
                            url: _this.url,
                            success: (data) => _this.onLoadPreview(data)
                        });
                    }
                },
                out: function () {}
            });

        _this.$previewBox
            .mouseenter(() => { keepVisible = true; })
            .mouseleave(() => _this.$previewBox.hide());
    }

    showInWindow () {
        // eslint-disable-next-line no-new
        new Window({
            content: this.$previewBoxWindow.html(),
            wide: true
        });
    };

    onLoadPreview (responseHtml) {
        this.isLoading = false;
        this.isLoaded = true;
        this.$previewBoxWindow.html(responseHtml);
        this.$previewBoxWindow.show();
        if (this.showInWindowAfterLoad) {
            this.showInWindow();
        }
    };

    static init ($container) {
        $container.filterAllNodes('.js-order-preview').each(function () {
            // eslint-disable-next-line no-new
            new OrderPreview($(this));
        });
    }
}

(new Register()).registerCallback(OrderPreview.init);
