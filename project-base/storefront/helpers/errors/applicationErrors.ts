export type ApplicationErrorVerbosityLevel = 'flash-message' | 'no-flash-message' | 'no-log';

const ApplicationErrors = {
    default: 'flash-message',
    'cart-not-found': 'flash-message',
    'max-allowed-limit': 'flash-message',
    'packetery-address-id-invalid': 'flash-message',
    'invalid-credentials': 'flash-message',
    'invalid-refresh-token': 'flash-message',
    'order-emails-not-sent': 'flash-message',
    'order-empty-cart': 'flash-message',
    'personal-data-request-type-invalid': 'flash-message',
    'blog-category-not-found': 'flash-message',
    'image-type-invalid': 'flash-message',
    'image-size-invalid': 'flash-message',
    'order-not-found': 'flash-message',
    'personal-data-hash-invalid': 'flash-message',
    'product-price-missing': 'flash-message',
    'no-result-found-for-slug': 'no-flash-message',
    'store-not-found': 'flash-message',
    'invalid-token': 'no-flash-message',
    'product-not-found': 'flash-message',
    'handling-with-logged-customer-comparison': 'flash-message',
    'comparison-not-found': 'flash-message',
    'compared-item-not-found': 'flash-message',
    'compared-item-already-exists': 'flash-message',
    'seo-page-not-found': 'no-log',
    'wishlist-not-found': 'flash-message',
    'wishlist-item-already-exists': 'flash-message',
    'wishlist-item-not-found': 'flash-message',
    'order-sent-page-not-available': 'no-log',
} as const;

type KeysMatching<T, V extends ApplicationErrorVerbosityLevel> = {
    [K in keyof T]: T[K] extends V ? K : never;
}[keyof T];

export type FlashMessageKeys = KeysMatching<typeof ApplicationErrors, 'flash-message'>;

export type NoFlashMessageKeys = KeysMatching<typeof ApplicationErrors, 'no-flash-message'>;

export type NoLogKeys = KeysMatching<typeof ApplicationErrors, 'no-log'>;

export type ApplicationErrorsType = keyof typeof ApplicationErrors;

export const isFlashMessageError = (errorCode: string): errorCode is FlashMessageKeys =>
    Object.keys(ApplicationErrors).includes(errorCode) &&
    ApplicationErrors[errorCode as keyof typeof ApplicationErrors] === 'flash-message';

export const isNoFlashMessageError = (errorCode: string): errorCode is NoFlashMessageKeys =>
    Object.keys(ApplicationErrors).includes(errorCode) &&
    ApplicationErrors[errorCode as keyof typeof ApplicationErrors] === 'no-flash-message';

export const isNoLogError = (errorCode: string): errorCode is NoLogKeys =>
    Object.keys(ApplicationErrors).includes(errorCode) &&
    ApplicationErrors[errorCode as keyof typeof ApplicationErrors] === 'no-log';
