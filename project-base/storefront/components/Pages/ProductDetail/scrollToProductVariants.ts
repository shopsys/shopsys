import { PRODUCT_VARIANTS_ID } from 'components/Blocks/Product/productActionConstants';

/**
 * Moves focus to the variant list of the main variant detail and scrolls it into view.
 * Returns false when the list is not rendered, so the caller can fall back to the default behavior.
 */
export const scrollToProductVariants = () => {
    const variantList = document.getElementById(PRODUCT_VARIANTS_ID);

    if (!variantList) {
        return false;
    }

    window.history.replaceState(window.history.state, '', `#${PRODUCT_VARIANTS_ID}`);
    variantList.focus({ preventScroll: true });
    variantList.scrollIntoView({
        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth',
        block: 'start',
    });

    return true;
};
