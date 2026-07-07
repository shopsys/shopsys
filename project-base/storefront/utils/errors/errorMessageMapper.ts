import { Translate } from 'next-translate';
import { FlashMessageKeys } from './applicationErrors';

const getErrorMessageTranslationString = (errorCode: FlashMessageKeys, t: Translate): string | undefined => {
    const ERROR_MESSAGES: Record<FlashMessageKeys, string> = {
        default: t('Unknown error.'),
        'access-denied': t('Access denied'),
        'cannot-remove-own-customer-user': t('Cannot delete own user'),
        'cart-not-found': t('Cart not found.'),
        'company-already-registered': t('Customer is already registered.'),
        'complaint-not-found': t('Complaint not found.'),
        'handling-with-logged-customer-comparison': t('Product not found.'),
        'invalid-credentials': t('Invalid credentials.'),
        'invalid-quantity': t('Complaint item quantity exceeds the order item quantity.'),
        'invalid-refresh-token': t('Invalid refresh token.'),
        'max-allowed-limit': t('Max allowed limit reached.'),
        'max-transaction-count-reached': t('Max transaction count reached.'),
        'order-emails-not-sent': t('Automatic order emails was not sent.'),
        'order-empty-cart': t('Cart is empty.'),
        'order-not-found': t('Order not found.'),
        'packetery-address-id-invalid': t('Invalid Packetery address id.'),
        'personal-data-hash-invalid': t('Invalid hash.'),
        'personal-data-request-type-invalid': t('Invalid request type.'),
        'product-not-found': t('Product not found.'),
        'product-price-missing': t('Product price is missing.'),
        'register-by-order-is-not-possible': t('It was not possible to create register new user from the order'),
        'store-not-found': t('Store not found.'),
        'too-many-login-attempts': t('Too many login attempts. Try again later.'),
        // Cart mutation errors
        'add-order-items-error': t('Could not prefill your cart.'),
        'add-to-cart-error': t('Unable to add product to cart.'),
        'payment-error': t('There was an error while changing the payment method.'),
        'promo-code-apply-error': t('There was an error while adding a promo code to the order.'),
        'promo-code-remove-error': t('There was an error while removing the promo code from the order.'),
        'remove-from-cart-error': t('Unable to remove product from cart.'),
        'transport-error': t('There was an error while changing the transport method.'),
    };

    return ERROR_MESSAGES[errorCode];
};

export const getErrorMessage = (errorCode: FlashMessageKeys, t: Translate): string => {
    const translationString = getErrorMessageTranslationString(errorCode, t);

    return translationString ?? t('Unknown error.');
};
