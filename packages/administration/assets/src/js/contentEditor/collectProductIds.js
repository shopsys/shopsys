/**
 * Ids of the products chosen in a products picker, in the order they are shown.
 * The picker keeps one hidden input per product.
 *
 * @param {Element} container
 * @returns {string[]}
 */
export default function collectProductIds(container) {
    return Array.from(container.querySelectorAll('input.js-products-picker-item-input'))
        .map(input => input.value)
        .filter(value => value !== '');
}
