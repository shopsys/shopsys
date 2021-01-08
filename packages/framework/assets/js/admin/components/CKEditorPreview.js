import Register from '../../common/utils/Register';

export default class CKEditorPreview {

    constructor ($ckEditorPreview) {
        const $editButton = $ckEditorPreview.children('.js-cke-preview-edit');

        if (this.isOverflown($ckEditorPreview)) {
            $ckEditorPreview.addClass('cke-preview-text-collapsed');
        }

        $ckEditorPreview.click(() => {
            $ckEditorPreview.hide();
            $editButton.hide();
        });
    }

    isOverflown ($element) {
        return $element[0].scrollHeight > $element[0].clientHeight;
    }

    static init ($container) {
        $container.filterAllNodes('.js-cke-preview').each(function () {
            // eslint-disable-next-line no-new
            new CKEditorPreview($(this));
        });
    }
}

(new Register()).registerCallback(CKEditorPreview.init, 'CKEditorPreview.init');
