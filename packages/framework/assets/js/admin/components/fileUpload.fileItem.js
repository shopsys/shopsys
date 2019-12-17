import { forceValidateElement } from '../validation/validation';

export default class FileItem {

    constructor (uploader, $file, loaded) {
        this.uploader = uploader;
        this.$file = $file;
        this.$label = $file.find('.js-file-upload-label');
        this.$name = $file.find('.js-file-upload-name-input');
        this.$deleteButton = $file.find('.js-file-upload-delete');
        this.$progress = $file.find('.js-file-upload-progress');
        this.$progressBar = $file.find('.js-file-upload-progress-bar');
        this.$progressBarValue = $file.find('.js-file-upload-progress-bar-value');
        this.$input = $file.find('.js-file-upload-input');
        this.$iconType = $file.find('.js-file-upload-icon-type');
        this.$imageThumbnail = $file.find('.js-file-upload-file-thumbnail');

        this.$progress.hide();
        this.$deleteButton.click(() => this.deleteItem());
        if (loaded !== true) {
            this.$iconType.hide();
            this.$imageThumbnail.hide();
        }
    }

    deleteItem () {
        this.uploader.deleteTemporaryFile(this.$input.val());
        this.$file.remove();
        forceValidateElement(this.uploader.$uploader);
    }

    setLabel (filename, fileSize) {
        const sizeInMB = Math.round(fileSize / 1000 / 1000 * 100) / 100; // https://en.wikipedia.org/wiki/Binary_prefix
        this.$label.text(filename + ' (' + sizeInMB + ' MB)');
    }

    setName (filename) {
        this.$name.val(filename);
    };

    setProgress (percent) {
        this.$progress.show();
        this.$progressBar.width(percent + '%');
        this.$progressBarValue.text(percent + '%');

        const _this = this;
        if (percent === 100) {
            setTimeout(function () {
                _this.$progress.fadeOut();
            }, 1000);
        }
    }

    setAsUploaded (filename, iconType, imageThumbnailUri) {
        this.$input.val(filename);
        this.setIconType(iconType);
        this.setImageThumbnail(imageThumbnailUri);
    }

    setImageThumbnail (imageThumbnailUri) {
        if (imageThumbnailUri !== null) {
            this.$imageThumbnail.attr('src', imageThumbnailUri).show();
        }
    }

    setIconType (iconType) {
        if (iconType !== null) {
            this.$iconType
                .attr('class', this.$iconType.attr('class').replace(/__icon-type__/g, iconType))
                .show();
        }
    }
}
