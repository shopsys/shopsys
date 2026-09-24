import type { ProductsSliderVariant } from './ProductsSlider';

export const getProductsSliderTwClass = (variant: ProductsSliderVariant) => {
    switch (variant) {
        case 'default':
            return 'auto-cols-[225px] sm:auto-cols-[60%] md:auto-cols-[45%] lg:auto-cols-[30%] vl:auto-cols-[25%] xl:auto-cols-[20%] xxl:auto-cols-[16.6667%]';
        case 'article':
            return 'auto-cols-[80%] sm:auto-cols-[60%] md:auto-cols-[45%] lg:auto-cols-[30%] vl:auto-cols-[33.33%]';
        case 'lastVisited':
            return 'auto-cols-[140px] sm:auto-cols-[30%] lg:auto-cols-[19.5%] vl:auto-cols-[14.5%] xl:auto-cols-[12.5%] xxl:auto-cols-[11.1111%]';
        case 'autocomplete':
            return 'auto-cols-[140px] sm:auto-cols-[32%] md:auto-cols-[24%] lg:auto-cols-[20%]';
        case 'basketPopup':
            return 'auto-cols-[225px] sm:auto-cols-[60%] md:auto-cols-[45%] lg:auto-cols-[30%] vl:auto-cols-[25%]';
    }
};
