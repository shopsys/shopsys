export const PRODUCT_LIST_CONTROLS_ELEMENT_ID = 'product-list-controls';
export const PRODUCT_LIST_HEADING_ELEMENT_ID = 'product-list-heading';

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

const FIXED_HEADER_HEIGHT_PROPERTY = '--fixed-header-height';

// the sorting and filter controls are already on the screen below the fixed header, so the page can stay where it is
const isProductListControlsInView = () => {
    const productListControlsElement = document.getElementById(PRODUCT_LIST_CONTROLS_ELEMENT_ID);

    if (!productListControlsElement) {
        return false;
    }

    const fixedHeaderHeight =
        Number.parseFloat(
            window.getComputedStyle(document.documentElement).getPropertyValue(FIXED_HEADER_HEIGHT_PROPERTY),
        ) || 0;
    const { top, bottom, height } = productListControlsElement.getBoundingClientRect();

    return height > 0 && top >= fixedHeaderHeight && bottom <= window.innerHeight;
};

export const scrollToProductListControls = (attempt = 0) => {
    if (typeof window === 'undefined' || typeof document === 'undefined') {
        return;
    }

    window.requestAnimationFrame(() => {
        if (typeof window === 'undefined' || typeof document === 'undefined') {
            return;
        }

        if (isProductListControlsInView()) {
            return;
        }

        const productListControlsElement =
            document.getElementById(PRODUCT_LIST_HEADING_ELEMENT_ID) ??
            document.getElementById(PRODUCT_LIST_CONTROLS_ELEMENT_ID);

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
