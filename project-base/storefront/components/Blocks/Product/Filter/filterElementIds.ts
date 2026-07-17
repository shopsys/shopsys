export const FILTER_SELECTED_PARAMETERS_ELEMENT_ID = 'filter-selected-parameters';

const PRODUCT_LIST_ELEMENT_ID = 'product-list';
const FILTER_SCROLL_ATTEMPTS = 6;

export const scrollToProductList = (attempt = 0) => {
    if (typeof window === 'undefined' || typeof document === 'undefined') {
        return;
    }

    window.requestAnimationFrame(() => {
        if (typeof window === 'undefined' || typeof document === 'undefined') {
            return;
        }

        const productListElement = document.getElementById(PRODUCT_LIST_ELEMENT_ID);

        if (productListElement) {
            productListElement.scrollIntoView({ behavior: 'smooth', block: 'start' });

            return;
        }

        if (attempt < FILTER_SCROLL_ATTEMPTS) {
            window.setTimeout(() => scrollToProductList(attempt + 1), 50);
        }
    });
};

export const scrollToSelectedFilters = (attempt = 0) => {
    if (typeof window === 'undefined' || typeof document === 'undefined') {
        return;
    }

    window.requestAnimationFrame(() => {
        if (typeof window === 'undefined' || typeof document === 'undefined') {
            return;
        }

        const selectedFiltersElement = document.getElementById(FILTER_SELECTED_PARAMETERS_ELEMENT_ID);

        if (selectedFiltersElement) {
            selectedFiltersElement.scrollIntoView({ behavior: 'smooth', block: 'start' });

            return;
        }

        if (attempt < FILTER_SCROLL_ATTEMPTS) {
            window.setTimeout(() => scrollToSelectedFilters(attempt + 1), 50);

            return;
        }

        scrollToProductList();
    });
};
