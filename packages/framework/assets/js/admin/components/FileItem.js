import $ from 'jquery';
import IconText from 'icons/tabler/file-text.svg?raw';
import IconDoc from 'icons/tabler/file-type-doc.svg?raw';
import IconPdf from 'icons/tabler/file-type-pdf.svg?raw';
import IconXls from 'icons/tabler/file-type-xls.svg?raw';
import IconXml from 'icons/tabler/file-type-xml.svg?raw';

export default class FileItem {
    constructor(uploader, $file, loaded) {
        this.uploader = uploader;
        this.$file = $file;
        this.$label = $file.find('.js-file-upload-label');
        this.$name = $file.find('.js-file-upload-name-input');
        this.$deleteButton = $file.find('.js-file-upload-delete');
        this.$progress = $file.find('.js-file-upload-progress');
        this.$progressBar = $file.find('.js-file-upload-progress-bar');
        this.$progressBarValue = $file.find('.js-file-upload-progress-bar-value');
        this.$input = $file.find('.js-file-upload-input');
        this.$itemContainer = $file.find('.js-list-files-item');
        this.$imageThumbnail = $file.find('.js-file-upload-file-thumbnail');

        this.$progress.hide();
        this.$deleteButton.click(() => this.deleteItem());
        if (loaded !== true) {
            this.$imageThumbnail.hide();
        }
    }

    deleteItem() {
        FileItem.removeError(this.$deleteButton);
        this.uploader.deleteTemporaryFile(this.$input.val());
        this.$file.remove();
    }

    setLabel(filename, fileSize) {
        const sizeInMB = Math.round((fileSize / 1000 / 1000) * 100) / 100; // https://en.wikipedia.org/wiki/Binary_prefix
        this.$label.text(`${filename} (${sizeInMB} MB)`);
    }

    setName(filename) {
        this.$name.val(filename);
    }

    setProgress(percent) {
        this.$progress.show();
        this.$progressBar.width(`${percent}%`);
        this.$progressBarValue.text(`${percent}%`);
        if (percent === 100) {
            setTimeout(() => {
                this.$progress.fadeOut();
            }, 1000);
        }
    }

    setAsUploaded(filename, iconType, imageThumbnailUri) {
        this.$input.val(filename);
        this.setIconType(iconType);
        this.setImageThumbnail(imageThumbnailUri);
    }

    setImageThumbnail(imageThumbnailUri) {
        if (imageThumbnailUri !== null) {
            this.$imageThumbnail.attr('src', imageThumbnailUri).show();
        }
    }

    setIconType(iconType) {
        if (iconType !== null) {
            const iconMap = {
                pdf: IconPdf,
                word: IconDoc,
                xml: IconXml,
                excel: IconXls,
            };

            const icon = iconMap[iconType] || IconText;

            const $icon = $(
                `<span class="d-flex align-items-center justify-content-center h-100 w-100 icon-wrapper-lg file-icon-${iconType}">${icon}</span>`,
            );

            this.$itemContainer.prepend($icon);
        }
    }

    static removeError($button) {
        $button.closest('.js-file-upload').siblings('.js-validation-errors-list').remove();
    }
}
