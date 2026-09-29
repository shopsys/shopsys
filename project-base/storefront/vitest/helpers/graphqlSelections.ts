import { type DocumentNode, type FieldNode, getOperationAST, Kind, type SelectionSetNode } from 'graphql';

export const getFields = (document: DocumentNode, selectionSet: SelectionSetNode): FieldNode[] =>
    selectionSet.selections.flatMap((selection) => {
        if (selection.kind === Kind.FIELD) {
            return [selection];
        }
        if (selection.kind === Kind.INLINE_FRAGMENT) {
            return getFields(document, selection.selectionSet);
        }
        const fragment = document.definitions.find(
            (definition) =>
                definition.kind === Kind.FRAGMENT_DEFINITION && definition.name.value === selection.name.value,
        );
        if (fragment?.kind !== Kind.FRAGMENT_DEFINITION) {
            throw new Error(`Missing fragment ${selection.name.value}`);
        }
        return getFields(document, fragment.selectionSet);
    });

export const getSelectionAt = (document: DocumentNode, path: string[]): SelectionSetNode => {
    const operation = getOperationAST(document);
    if (!operation) {
        throw new Error('Missing operation');
    }
    let selectionSet: SelectionSetNode = operation.selectionSet;
    for (const name of path) {
        const selections = getFields(document, selectionSet)
            .filter((field) => (field.alias?.value ?? field.name.value) === name)
            .flatMap((field) => field.selectionSet?.selections ?? []);
        if (selections.length === 0) {
            throw new Error(`Missing selection ${path.join('.')}`);
        }
        selectionSet = { kind: Kind.SELECTION_SET, selections };
    }
    return selectionSet;
};
