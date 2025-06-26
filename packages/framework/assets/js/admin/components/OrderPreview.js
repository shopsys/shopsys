import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';
import Window from '../utils/Window';

export default class OrderPreview {
    constructor($orderPreview) {
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
        this.$previewIcon
            .mouseleave(() => {
                keepVisible = false;
                setTimeout(() => {
                    if (!keepVisible) {
                        this.$previewBox.hide();
                    }
                }, 20); // Mouse needs some time to leave the icon and enter the $visibilityBox
            })
            .click(() => {
                if (isLoaded) {
                    this.showInWindow();
                } else {
                    this.showInWindowAfterLoad = true;
                }
            })
            .hoverIntent({
                interval: 200,
                over: () => {
                    this.$previewBox.show();
                    $('body').find('.js-table-grid').removeClass(overflowHiddenClass).addClass(overflowVisibleClass);
                    $('body').find('.js-table-touch').removeClass(overflowHiddenClass).addClass(overflowVisibleClass);

                    if (!this.isLoaded && !this.isLoading) {
                        this.isLoading = true;
                        Ajax.ajax({
                            loaderElement: 'none',
                            url: this.url,
                            success: data => this.onLoadPreview(data),
                        });
                    }
                },
                out: () => {
                    $('body').find('.js-table-grid').removeClass(overflowVisibleClass).addClass(overflowHiddenClass);
                    $('body').find('.js-table-touch').removeClass(overflowVisibleClass).addClass(overflowHiddenClass);
                },
            });

        this.$previewBox
            .mouseenter(() => {
                keepVisible = true;
            })
            .mouseleave(() => this.$previewBox.hide());
    }

    showInWindow() {
        // eslint-disable-next-line no-new
        new Window({
            content: this.$previewBoxWindow.html(),
            wide: true,
        });
    }

    onLoadPreview(responseHtml) {
        const windowPreviewThreshold = 500;
        this.isLoading = false;
        this.isLoaded = true;
        this.$previewBoxWindow.html(responseHtml);
        this.$previewBoxWindow.show(function () {
            const tableHeight = $('body').find('.js-table-grid').height();
            if (tableHeight > windowPreviewThreshold) {
                const tablePosition = $('body').find('.js-table-grid').offset().top;
                const popupWindowPosition = $(this).offset().top;

                if (tablePosition + tableHeight - popupWindowPosition < windowPreviewThreshold) {
                    $(this).addClass('bottom');
                }
            }
        });

        if (this.showInWindowAfterLoad) {
            this.showInWindow();
        }
    }

    static init($container) {
        $container.filterAllNodes('.js-order-preview').each(function () {
            // eslint-disable-next-line no-new
            new OrderPreview($(this));
        });
    }
}

new Register().registerCallback(OrderPreview.init, 'OrderPreview.init');
