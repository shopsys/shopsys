import formChangeInfo from './formChangeInfo';
import { select, deselect, getSelectedValues } from './components/choiceControl';
import Register from '../common/register';

export default class FileUpload {

    static initDelete () {

        $('.js-file-upload-file').each(function () {
            const $file = $(this);
            const $filePreview = $file.find('.js-file-upload-preview');
            const $deleteButton = $file.find('.js-file-upload-delete-button');
            const $revertButton = $file.find('.js-file-upload-delete-revert-button');
            const $deleteInfo = $file.find('.js-file-upload-file-overlay');
            const fileId = $file.data('id');

            $deleteButton.on('click.deleteFile', () => {
                select($file.data('delete-input'), fileId);
                $filePreview.addClass('list-files__item__in--removed');
                $deleteButton.hide();
                $revertButton.show();
                $deleteInfo.show();
                formChangeInfo.showInfo();
                return false;
            });

            $revertButton.bind('click.deleteFile', () => {
                deselect($file.data('delete-input'), fileId);
                $filePreview.removeClass('list-files__item__in--removed');
                $deleteButton.show();
                $revertButton.hide();
                $deleteInfo.hide();
                return false;
            });

            const fileIds = getSelectedValues($file.data('delete-input'));
            if ($.inArray(fileId, fileIds) !== -1) {
                $deleteButton.trigger('click.deleteFile');
            }
        });
    }

    static initSort () {
        $('.js-file-upload').sortable({
            handle: '.js-file-upload-file-handle',
            update: formChangeInfo.showInfo
        });
    }

    static init () {
        FileUpload.initDelete();
        FileUpload.initSort();
    }

}

(new Register()).registerCallback(FileUpload.init);
