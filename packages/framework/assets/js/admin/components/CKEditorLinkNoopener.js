// rel="noopener" is added to links with target="_blank" whenever the content is saved through CKEditor,
// both the classic editor and the one embedded in GrapesJS
import { addRelNoopenerWhenTargetIsBlank } from '../../common/utils/addRelNoopenerWhenTargetIsBlank';
import Register from '../../common/utils/Register';

function initCKEditorLinkNoopener() {
    if (typeof CKEDITOR === 'undefined') {
        return;
    }

    if (CKEDITOR._linkNoopenerRegistered) {
        return;
    }
    CKEDITOR._linkNoopenerRegistered = true;

    CKEDITOR.on('instanceReady', event => {
        event.editor.dataProcessor.htmlFilter.addRules({
            elements: {
                a: element => {
                    const rel = addRelNoopenerWhenTargetIsBlank(element.attributes.rel, element.attributes.target);

                    if (rel !== element.attributes.rel) {
                        element.attributes.rel = rel;
                    }
                },
            },
        });
    });
}

new Register().registerCallback(initCKEditorLinkNoopener, 'CKEditorLinkNoopener');
