import {
    getCurrentVisibleSliderItems,
    getProductsSliderTwClass,
    VISIBLE_SLIDER_ITEMS,
    VISIBLE_SLIDER_ITEMS_BASKET_POPUP,
} from 'components/Blocks/Product/ProductsSlider';
import { describe, expect, test } from 'vitest';

describe('ProductsSlider', () => {
    test('keeps the basket popup responsive and caps it at four desktop columns', () => {
        const basketPopupClasses = getProductsSliderTwClass('basketPopup');

        expect(basketPopupClasses).toContain('sm:auto-cols-[60%]');
        expect(basketPopupClasses).toContain('md:auto-cols-[45%]');
        expect(basketPopupClasses).toContain('lg:auto-cols-[30%]');
        expect(basketPopupClasses).toContain('vl:auto-cols-[25%]');
        expect(basketPopupClasses).not.toContain('xl:auto-cols-[20%]');
        expect(basketPopupClasses).not.toContain('xxl:auto-cols-[16.6667%]');
    });

    test('uses four visible items for basket popup controls on large viewports', () => {
        const currentVisibleItems = getCurrentVisibleSliderItems({
            visibleSliderItems: VISIBLE_SLIDER_ITEMS_BASKET_POPUP,
            variant: 'basketPopup',
            isMobile: false,
            isLargeDesktop: true,
            isVeryLargeDesktop: true,
        });

        expect(currentVisibleItems).toBe(4);
    });

    test('keeps six visible items in the default slider on very large viewports', () => {
        const currentVisibleItems = getCurrentVisibleSliderItems({
            visibleSliderItems: VISIBLE_SLIDER_ITEMS,
            variant: 'default',
            isMobile: false,
            isLargeDesktop: true,
            isVeryLargeDesktop: true,
        });

        expect(currentVisibleItems).toBe(6);
    });
});
