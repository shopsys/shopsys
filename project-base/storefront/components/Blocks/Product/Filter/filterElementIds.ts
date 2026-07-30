export const PRODUCT_LIST_CONTROLS_ELEMENT_ID = 'product-list-controls';

const PRODUCT_LIST_ELEMENT_ID = 'product-list';
const FILTER_SCROLL_ATTEMPTS = 6;

const scrollToProductList = (attempt = 0) => {
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

export const scrollToProductListControls = (attempt = 0) => {
    if (typeof window === 'undefined' || typeof document === 'undefined') {
        return;
    }

    window.requestAnimationFrame(() => {
        if (typeof window === 'undefined' || typeof document === 'undefined') {
            return;
        }

        const productListControlsElement = document.getElementById(PRODUCT_LIST_CONTROLS_ELEMENT_ID);

        if (productListControlsElement) {
            productListControlsElement.scrollIntoView({ behavior: 'smooth', block: 'start' });

            return;
        }

        if (attempt < FILTER_SCROLL_ATTEMPTS) {
            window.setTimeout(() => scrollToProductListControls(attempt + 1), 50);

            return;
        }

        scrollToProductList();
    });
};
