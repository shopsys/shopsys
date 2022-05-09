export const ApplicationErrors = {
    default: 'default',
    'cart-not-found': 'cart-not-found',
    'max-allowed-limit': 'max-allowed-limit',
    'packetery-address-id-invalid': 'packetery-address-id-invalid',
    'invalid-credentials': 'invalid-credentials',
    'invalid-refresh-token': 'invalid-refresh-token',
    'order-emails-not-sent': 'order-emails-not-sent',
    'order-empty-cart': 'order-empty-cart',
    'personal-data-request-type-invalid': 'personal-data-request-type-invalid',
    'blog-category-not-found': 'blog-category-not-found',
    'image-type-invalid': 'image-type-invalid',
    'image-size-invalid': 'image-size-invalid',
    'order-not-found': 'order-not-found',
    'personal-data-hash-invalid': 'personal-data-hash-invalid',
    'product-price-missing': 'product-price-missing',
    'no-result-found-for-slug': 'no-result-found-for-slug',
    'store-not-found': 'store-not-found',
} as const;

export type ApplicationErrorsType = keyof typeof ApplicationErrors;
