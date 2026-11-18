/**
 * @jest-environment jsdom
 */

import { highlightVisibleFilterParametersLimit } from './VisibleFilterParametersLimit';

const createItems = count => {
    const container = document.createElement('div');

    for (let i = 0; i < count; i++) {
        const item = document.createElement('div');
        item.className = 'js-sortable-values-item';
        container.append(item);
    }

    return container;
};

const getHighlightedItems = container =>
    [...container.children]
        .map((item, index) => ({
            index,
            after: item.classList.contains('sortable-values-item-limit-after'),
            before: item.classList.contains('sortable-values-item-limit-before'),
        }))
        .filter(({ after, before }) => after || before);

test('line is shown after the last parameter displayed in the sidebar', () => {
    const container = createItems(5);

    highlightVisibleFilterParametersLimit(container, '2');

    expect(getHighlightedItems(container)).toEqual([{ index: 1, after: true, before: false }]);
});

test('line is shown before the first parameter when no parameter is displayed in the sidebar', () => {
    const container = createItems(3);

    highlightVisibleFilterParametersLimit(container, '0');

    expect(getHighlightedItems(container)).toEqual([{ index: 0, after: false, before: true }]);
});

test.each(['3', '10', '', '-1', '1.5', 'abc'])('no line is shown for count "%s" with three parameters', count => {
    const container = createItems(3);

    highlightVisibleFilterParametersLimit(container, count);

    expect(getHighlightedItems(container)).toEqual([]);
});

test('changing the count moves the line', () => {
    const container = createItems(4);

    highlightVisibleFilterParametersLimit(container, '1');
    highlightVisibleFilterParametersLimit(container, '3');

    expect(getHighlightedItems(container)).toEqual([{ index: 2, after: true, before: false }]);
});
