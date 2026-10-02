import $ from 'jquery';
import Sortable from 'sortablejs';
import Register from '../../common/utils/Register';
import { deselect, getSelectedValues, select } from './choiceControl';
import formChangeInfo from './FormChangeInfo';

export default class FileUploadPreview {
    static initDelete() {
        $('.js-file-upload-file').each(function () {
            const $file = $(this);
            const $deleteButton = $file.find('.js-file-upload-delete-button');
            const $revertButton = $file.find('.js-file-upload-delete-revert-button');
            const $deleteInfo = $file.find('.js-file-upload-file-overlay');
            const fileId = $file.data('id');

            $deleteButton.on('click.deleteFile', () => {
                select($file.data('delete-input'), fileId);
                $deleteButton.hide();
                $revertButton.show();
                $deleteInfo.addClass('d-flex').removeClass('d-none');
                formChangeInfo.showInfo();
                return false;
            });

            $revertButton.on('click.deleteFile', () => {
                deselect($file.data('delete-input'), fileId);
                $deleteButton.show();
                $revertButton.hide();
                $deleteInfo.removeClass('d-flex').addClass('d-none');
                return false;
            });

            const fileIds = getSelectedValues($file.data('delete-input'));
            if ($.inArray(fileId, fileIds) !== -1) {
                $deleteButton.trigger('click.deleteFile');
            }
        });
    }

    static initSort() {
        document.querySelectorAll('.js-file-upload').forEach(element => {
            Sortable.create(element, {
                handle: '.js-file-upload-file-handle',
                animation: 150,
                onUpdate: formChangeInfo.showInfo,
                ghostClass: 'opacity-50',
                chosenClass: 'border-primary',
                dragClass: 'shadow-lg',
                scroll: true,
                scrollSensitivity: 100,
                scrollSpeed: 400,
            });
        });
    }

    static init() {
        FileUploadPreview.initDelete();
        FileUploadPreview.initSort();
    }
}

new Register().registerCallback(FileUploadPreview.init, 'FileUploadPreview.init');
