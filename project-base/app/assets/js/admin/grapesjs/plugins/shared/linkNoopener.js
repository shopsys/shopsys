import { addRelNoopenerWhenTargetIsBlank } from 'framework/common/utils/addRelNoopenerWhenTargetIsBlank';

// the target attribute set through the trait panel never passes through the CKEditor content,
// so the editor filter cannot see it
export const addRelNoopenerToComponentWithBlankTarget = component => {
    const attributes = component.getAttributes();
    const rel = addRelNoopenerWhenTargetIsBlank(attributes.rel, attributes.target);

    if (rel !== attributes.rel) {
        component.addAttributes({ rel });
    }
};
