import $ from 'jquery';
import Register from '../../common/utils/Register';

export default class CKEditorPreview {
    constructor($ckEditorPreview) {
        const $editButton = $ckEditorPreview.children('.js-cke-preview-edit');

        $ckEditorPreview.on('click', 'a', e => {
            e.preventDefault();
        });

        $ckEditorPreview.click(() => {
            $ckEditorPreview.hide();
            $editButton.hide();
        });
    }

    static init($container) {
        $container.filterAllNodes('.js-cke-preview').each(function () {
            // eslint-disable-next-line no-new
            new CKEditorPreview($(this));
        });
    }
}

new Register().registerCallback(CKEditorPreview.init, 'CKEditorPreview.init');
