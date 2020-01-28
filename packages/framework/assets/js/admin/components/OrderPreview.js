import Ajax from '../../common/utils/Ajax';
import Window from '../utils/Window';
import Register from '../../common/utils/Register';

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
                    _this.$previewBox.parents('table').css({'overflow': 'visible'});
                    _this.$previewBox.parents('table').parent().css({'overflow': 'visible'});

                    if (!_this.isLoaded && !_this.isLoading) {
                        _this.isLoading = true;
                        Ajax.ajax({
                            loaderElement: null,
                            url: _this.url,
                            success: (data) => _this.onLoadPreview(data)
                        });
                    }
                },
                out: function () {
                    _this.$previewBox.parents('table').css({'overflow': 'hidden'});
                    _this.$previewBox.parents('table').parent().css({'overflow': 'hidden'});
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
        this.isLoading = false;
        this.isLoaded = true;
        this.$previewBoxWindow.html(responseHtml);
        this.$previewBoxWindow.show(function(){
            let tableHeight = $(this).parents('table').height();
            if(tableHeight > 500){
                let tablePosition = $(this).parents('table').offset().top;
                let popupWindowPosition = $(this).offset().top;

                if(((tablePosition + tableHeight) - popupWindowPosition) < 500){
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
