type ApplicationErrorVerbosityLevel = 'flash-message' | 'no-flash-message' | 'no-log';

const ApplicationErrors = {
    // Flash-message codes (user needs to know)
    default: 'flash-message',
    'access-denied': 'flash-message',
    'cannot-remove-own-customer-user': 'flash-message',
    'cart-not-found': 'flash-message',
    'company-already-registered': 'flash-message',
    'complaint-not-found': 'flash-message',
    'handling-with-logged-customer-comparison': 'flash-message',
    'invalid-credentials': 'flash-message',
    'invalid-quantity': 'flash-message',
    'invalid-refresh-token': 'flash-message',
    'max-allowed-limit': 'flash-message',
    'max-transaction-count-reached': 'flash-message',
    'order-emails-not-sent': 'flash-message',
    'order-empty-cart': 'flash-message',
    'order-not-found': 'flash-message',
    'packetery-address-id-invalid': 'flash-message',
    'personal-data-hash-invalid': 'flash-message',
    'personal-data-request-type-invalid': 'flash-message',
    'product-not-found': 'flash-message',
    'product-price-missing': 'flash-message',
    'register-by-order-is-not-possible': 'flash-message',
    'store-not-found': 'flash-message',
    'too-many-login-attempts': 'flash-message',
    'too-many-store-search-attempts': 'flash-message',
    // Cart mutation errors - handled by hooks, messages centralized here
    'add-order-items-error': 'flash-message',
    'add-to-cart-error': 'flash-message',
    'payment-error': 'flash-message',
    'promo-code-apply-error': 'flash-message',
    'promo-code-remove-error': 'flash-message',
    'remove-from-cart-error': 'flash-message',
    'transport-error': 'flash-message',

    // No-flash-message codes (logged but no toast shown to user)
    'advert-position-without-category': 'no-flash-message',
    'article-not-found': 'no-flash-message',
    'article-not-found-privacy-policy': 'no-flash-message',
    'article-not-found-terms-and-conditions': 'no-flash-message',
    'article-not-found-user-consent-policy': 'no-flash-message',
    'blog-article-not-found': 'no-flash-message',
    'blog-category-not-found': 'no-flash-message',
    'brand-not-found': 'no-flash-message',
    'cart-item-invalid': 'no-flash-message',
    'cart-unavailable': 'no-flash-message',
    'category-not-found': 'no-flash-message',
    'COMPARISON-product-list-not-found': 'no-flash-message',
    'country-not-found': 'no-flash-message',
    'customer-user-not-found': 'no-flash-message',
    'customer-user-not-logged': 'no-flash-message',
    'delivery-address-not-found': 'no-flash-message',
    'flag-not-found': 'no-flash-message',
    'invalid-access': 'no-flash-message',
    'invalid-argument': 'no-flash-message',
    'invalid-find-criteria-for-product-list': 'no-flash-message',
    'last-customer-user-with-default-role-group': 'no-flash-message',
    'mail-failed': 'no-flash-message',
    'missing-complaint-items': 'no-flash-message',
    'order-already-paid': 'no-flash-message',
    'order-cancelled': 'no-flash-message',
    'order-item-not-found': 'no-flash-message',
    'order-process-payment': 'no-flash-message',
    'order-withdrawal-already-requested': 'no-flash-message',
    'order-withdrawal-deadline-passed': 'no-flash-message',
    'payment-not-found': 'no-flash-message',
    'product-already-in-list': 'no-flash-message',
    'product-list-not-found': 'no-flash-message',
    'product-not-in-list': 'no-flash-message',
    'ready-category-seo-mix-not-found': 'no-flash-message',
    'transport-not-found': 'no-flash-message',
    'unable-to-generate-breadcrumb-items': 'no-flash-message',
    'WISHLIST-product-list-not-found': 'no-flash-message',

    // No-log codes (expected errors, handled by specific code)
    'COMPARISON-product-already-in-list': 'no-log',
    'COMPARISON-product-not-in-list': 'no-log',
    'expired-token': 'no-log',
    'invalid-account-or-password': 'no-log',
    'invalid-token': 'no-log',
    'no-result-found-for-slug': 'no-log',
    'order-sent-page-not-available': 'no-log',
    'seo-page-not-found': 'no-log',
    'WISHLIST-product-already-in-list': 'no-log',
    'WISHLIST-product-not-in-list': 'no-log',
} as const;

type KeysMatching<T, V extends ApplicationErrorVerbosityLevel> = {
    [K in keyof T]: T[K] extends V ? K : never;
}[keyof T];

export type FlashMessageKeys = KeysMatching<typeof ApplicationErrors, 'flash-message'>;

type NoFlashMessageKeys = KeysMatching<typeof ApplicationErrors, 'no-flash-message'>;

type NoLogKeys = KeysMatching<typeof ApplicationErrors, 'no-log'>;

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
