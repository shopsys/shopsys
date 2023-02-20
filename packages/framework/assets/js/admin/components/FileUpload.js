import './jquery.dmuploader';
import FileItem from './FileItem';
import Window from '../utils/Window';
import { forceValidateElement } from '../../common/validation/validationHelpers';
import formChangeInfo from './FormChangeInfo';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';
import Translator from 'bazinga-translator';

export default class FileUpload {

    constructor ($uploader) {
        this.$uploadedFiles = $uploader.find('.js-file-upload-uploaded-files');
        this.$status = $uploader.find('.js-file-upload-status');
        this.$fallbackHide = $uploader.find('.js-file-upload-fallback-hide');
        this.multiple = $uploader.find('input[type=file]').attr('multiple') === 'multiple';
        this.deleteUrl = $uploader.data('fileupload-delete-url');
        this.ready = true;
        this.items = [];
        this.lastUploadItemId = null;
        this.$uploader = $uploader;

        this.$uploader.closest('form').submit((event) => this.onFormSubmit(event));
        this.initUploadedFiles();
        this.initUploader();
    }

    onFormSubmit (event) {
        if (!this.ready) {
            // eslint-disable-next-line no-new
            new Window({
                content: Translator.trans('Please wait until all files are uploaded and try again.')
            });
            event.preventDefault();
        }
    }

    initUploadedFiles () {
        const _this = this;
        this.$uploadedFiles.find('.js-file-upload-uploaded-file').each(function () {
            // eslint-disable-next-line no-new
            new FileItem(_this, $(this), true);
        });
    }

    initUploader () {
        const _this = this;
        this.$uploader.dmUploader({
            url: this.$uploader.data('fileupload-url'),
            dataType: 'json',
            onBeforeUpload: (id) => _this.onBeforeUpload(id),
            onNewFile: (id, file) => _this.onUploadNewFile(id, file),
            onComplete: () => _this.onUploadComplete(),
            onUploadProgress: (id, percent) => _this.onUploadProgress(id, percent),
            onUploadSuccess: (id, data) => _this.onUploadSuccess(id, data),
            onUploadError: (id, message, code) => _this.onUploadError(id, message, code),
            onFallbackMode: () => _this.onFallbackMode()
        });
    }

    onBeforeUpload (id) {
        this.ready = false;
        this.updateFileStatus('uploading', Translator.trans('Uploading...'));
    }

    updateFileStatus (status, message) {
        this.$status.parent().stop(true, true).show();
        this.$status.text(message).removeClass('error success uploading').addClass(status);
    }

    onUploadNewFile (id, file) {
        const $uploadedFile = this.createNewUploadedFile();
        $uploadedFile.show();
        this.items[id] = new FileItem(this, $uploadedFile);
        this.items[id].setLabel(file.name, file.size);
        this.items[id].setName(file.name.split('.').slice(0, -1).join('.'));
        this.$uploadedFiles.append($uploadedFile);
    }

    createNewUploadedFile () {
        const countAddedNewUploadedFiles = this.$uploadedFiles.find('.js-file-upload-uploaded-file-template').length;
        const templateHtml = this.$uploadedFiles.data('prototype').replace(/__name__/g, countAddedNewUploadedFiles);
        const $uploadedFileTemplate = $($.parseHTML(templateHtml));
        $uploadedFileTemplate.find('*[id]').removeAttr('id');

        return $uploadedFileTemplate;
    }

    onUploadComplete () {
        this.ready = true;
        forceValidateElement(this.$uploader);
    }

    onUploadProgress (id, percent) {
        this.items[id].setProgress(percent);
        this.updateFileStatus('uploading', Translator.trans('Uploading...'));
    }

    onUploadSuccess (id, data) {
        if (data.status === 'success') {
            if (this.lastUploadItemId !== null && this.multiple === false) {
                this.items[this.lastUploadItemId].deleteItem();
            }
            this.lastUploadItemId = id;
            this.items[id].setAsUploaded(data.filename, data.iconType, data.imageThumbnailUri);
            this.updateFileStatus('success', Translator.trans('Successfully uploaded'));
            this.$status.parent().fadeOut(4000);
            formChangeInfo.showInfo();
        } else {
            this.items[id].deleteItem();
            // eslint-disable-next-line no-new
            new Window({
                content: Translator.trans('Error occurred while uploading file.')
            });
        }
    }

    onUploadError (id, message, code) {
        this.items[id].deleteItem();
        if (code === 413) {
            message = Translator.trans('File is too big');
        } else if (code === 415) {
            message = Translator.trans('File is in unsupported format');
        }
        // eslint-disable-next-line no-new
        new Window({
            content: Translator.trans('Error occurred while uploading file: %message%', { 'message': message })
        });
        this.$status.parent().hide();
    }

    onFallbackMode () {
        this.$fallbackHide.hide();
    }

    deleteTemporaryFile (filename) {
        Ajax.ajax({
            url: this.deleteUrl,
            type: 'POST',
            data: { filename: filename },
            dataType: 'json'
        });
    }

    static init ($container) {
        $container.filterAllNodes('.js-file-upload').each(function () {
            // eslint-disable-next-line no-new
            new FileUpload($(this));

        });
    }
}

(new Register()).registerCallback(FileUpload.init, 'FileUpload.init');
