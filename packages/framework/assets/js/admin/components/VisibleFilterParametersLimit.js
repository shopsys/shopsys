import Register from '../../common/utils/Register';

const ITEM_SELECTOR = '.js-sortable-values-item';
const LIMIT_AFTER_CLASS = 'sortable-values-item-limit-after';
const LIMIT_BEFORE_CLASS = 'sortable-values-item-limit-before';

export function highlightVisibleFilterParametersLimit(itemsContainer, rawCount) {
    const items = [...itemsContainer.querySelectorAll(ITEM_SELECTOR)];
    const count = Number(rawCount);

    for (const item of items) {
        item.classList.remove(LIMIT_AFTER_CLASS, LIMIT_BEFORE_CLASS);
    }

    if (rawCount === '' || !Number.isInteger(count) || count < 0 || count >= items.length) {
        return;
    }

    if (count === 0) {
        items[0].classList.add(LIMIT_BEFORE_CLASS);

        return;
    }

    items[count - 1].classList.add(LIMIT_AFTER_CLASS);
}

function initVisibleFilterParametersLimit($container) {
    $container.filterAllNodes('[data-visible-filter-parameters-order]').each((_index, orderSelect) => {
        const form = orderSelect.closest('form');
        const countInput = form?.querySelector('[data-visible-filter-parameters-count]');
        const itemsContainer = orderSelect
            .closest('.js-sortable-values-container')
            ?.querySelector('.js-sortable-values-items');

        if (!countInput || !itemsContainer) {
            return;
        }

        const update = () => highlightVisibleFilterParametersLimit(itemsContainer, countInput.value);

        countInput.addEventListener('input', update);
        countInput.addEventListener('change', update);
        new MutationObserver(update).observe(itemsContainer, { childList: true });
        update();
    });
}

new Register().registerCallback(initVisibleFilterParametersLimit, 'initVisibleFilterParametersLimit');
