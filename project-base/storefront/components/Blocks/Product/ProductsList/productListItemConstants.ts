import type { ProductVisibleItemsConfigType } from './ProductListItem';

export const PREDEFINED_VISIBLE_ITEMS_CONFIGS = {
    largeItem: {
        productListButtons: true,
        addToCart: true,
        flags: true,
        discount: false,
        price: true,
        storeAvailability: true,
        priceFromWord: true,
        reviews: true,
    } as ProductVisibleItemsConfigType,
    mediumItem: {
        flags: true,
        discount: false,
        price: true,
        storeAvailability: true,
        priceFromWord: true,
        reviews: true,
    } as ProductVisibleItemsConfigType,
} as const;
