(function ($) {

    Shopsys = window.Shopsys || {};
    Shopsys.fileUpload = Shopsys.fileUpload || {};

    Shopsys.fileUpload.initDelete = function () {

        $('.js-file-upload-file').each(function () {
            var $file = $(this);
            var $filePreview = $file.find('.js-file-upload-preview');
            var $deleteButton = $file.find('.js-file-upload-delete-button');
            var $revertButton = $file.find('.js-file-upload-delete-revert-button');
            var $deleteInfo = $file.find('.js-file-upload-file-overlay');
            var fileId = $file.data('id');

            $deleteButton.bind('click.deleteFile', function () {
                Shopsys.choiceControl.select($file.data('delete-input'), fileId);
                $filePreview.addClass('list-files__item__in--removed');
                $deleteButton.hide();
                $revertButton.show();
                $deleteInfo.show();
                Shopsys.formChangeInfo.showInfo();
                return false;
            });

            $revertButton.bind('click.deleteFile', function () {
                Shopsys.choiceControl.deselect($file.data('delete-input'), fileId);
                $filePreview.removeClass('list-files__item__in--removed');
                $deleteButton.show();
                $revertButton.hide();
                $deleteInfo.hide();
                return false;
            });

            var fileIds = Shopsys.choiceControl.getSelectedValues($file.data('delete-input'));
            if ($.inArray(fileId, fileIds) !== -1) {
                $deleteButton.trigger('click.deleteFile');
            }
        });
    };

    Shopsys.fileUpload.initSort = function () {
        $('.js-file-upload').sortable({
            handle: '.js-file-upload-file-handle',
            update: Shopsys.formChangeInfo.showInfo
        });
    };

    $(document).ready(function () {
        Shopsys.fileUpload.initDelete();
        Shopsys.fileUpload.initSort();
    });

})(jQuery);
