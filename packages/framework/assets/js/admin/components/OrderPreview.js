import Ajax from '../../common/utils/Ajax';
import Window from '../utils/Window';
import Register from '../../common/utils/Register';

export default class OrderPreview {

    constructor ($orderPreview) {
        const overflowHiddenClass = 'overflow-hidden';
        const overflowVisibleClass = 'overflow-visible';

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
                    $('body').find('.js-table-grid').removeClass(overflowHiddenClass).addClass(overflowVisibleClass);
                    $('body').find('.js-table-touch').removeClass(overflowHiddenClass).addClass(overflowVisibleClass);

                    if (!_this.isLoaded && !_this.isLoading) {
                        _this.isLoading = true;
                        Ajax.ajax({
                            loaderElement: 'none',
                            url: _this.url,
                            success: (data) => _this.onLoadPreview(data)
                        });
                    }
                },
                out: function () {
                    $('body').find('.js-table-grid').removeClass(overflowVisibleClass).addClass(overflowHiddenClass);
                    $('body').find('.js-table-touch').removeClass(overflowVisibleClass).addClass(overflowHiddenClass);
                }
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
    }

    onLoadPreview (responseHtml) {
        const windowPreviewThreshold = 500;
        this.isLoading = false;
        this.isLoaded = true;
        this.$previewBoxWindow.html(responseHtml);
        this.$previewBoxWindow.show(function () {
            const tableHeight = $('body').find('.js-table-grid').height();
            if (tableHeight > windowPreviewThreshold) {
                let tablePosition = $('body').find('.js-table-grid').offset().top;
                let popupWindowPosition = $(this).offset().top;

                if (((tablePosition + tableHeight) - popupWindowPosition) < windowPreviewThreshold) {
                    $(this).addClass('bottom');
                }
            }
        });

        if (this.showInWindowAfterLoad) {
            this.showInWindow();
        }
    }

    static init ($container) {
        $container.filterAllNodes('.js-order-preview').each(function () {
            // eslint-disable-next-line no-new
            new OrderPreview($(this));
        });
    }
}

(new Register()).registerCallback(OrderPreview.init, 'OrderPreview.init');
