import collectProductIds from './collectProductIds';

describe('collectProductIds', () => {
    test('returns ids of the hidden inputs in DOM order', () => {
        document.body.innerHTML = `
            <div id="picker">
                <input class="js-products-picker-item-input" value="7">
                <input class="js-products-picker-item-input" value="3">
                <input class="other" value="9">
            </div>`;

        expect(collectProductIds(document.getElementById('picker'))).toEqual(['7', '3']);
    });

    test('ignores inputs without a value', () => {
        document.body.innerHTML = '<div id="picker"><input class="js-products-picker-item-input" value=""></div>';

        expect(collectProductIds(document.getElementById('picker'))).toEqual([]);
    });
});
