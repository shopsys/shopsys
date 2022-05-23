import { ApplicationErrors, ApplicationErrorsType } from './applicationErrors';
import { Translate } from 'next-translate';

const ERROR_MESSAGES: Record<ApplicationErrorsType, string | undefined> = {
    [ApplicationErrors.default]: 'Unknown error.',
    [ApplicationErrors['cart-not-found']]: 'Cart not found.',
    [ApplicationErrors['max-allowed-limit']]: 'Max allowed limit reached.',
    [ApplicationErrors['packetery-address-id-invalid']]: 'Invalid Packetery address id.',
    [ApplicationErrors['invalid-credentials']]: 'Invalid credentials.',
    [ApplicationErrors['invalid-refresh-token']]: 'Invalid refresh token.',
    [ApplicationErrors['order-emails-not-sent']]: 'Automatic order emails was not sent.',
    [ApplicationErrors['order-empty-cart']]: 'Cart is empty.',
    [ApplicationErrors['personal-data-request-type-invalid']]: 'Invalid request type.',
    [ApplicationErrors['blog-category-not-found']]: 'Category not found.',
    [ApplicationErrors['image-type-invalid']]: 'Invalid image type.',
    [ApplicationErrors['image-size-invalid']]: 'Invalid image size.',
    [ApplicationErrors['order-not-found']]: 'Order not found.',
    [ApplicationErrors['personal-data-hash-invalid']]: 'Invalid hash.',
    [ApplicationErrors['product-price-missing']]: 'Product price is missing.',
    [ApplicationErrors['no-result-found-for-slug']]: 'No result found for slug.',
    [ApplicationErrors['store-not-found']]: 'Store not found.',
};

export const hasErrorMessage = (errorCode: string): boolean => {
    return ERROR_MESSAGES[errorCode as ApplicationErrorsType] !== undefined;
};

export const getErrorMessage = (errorCode: string, t: Translate): string => {
    const translationString = ERROR_MESSAGES[errorCode as ApplicationErrorsType];

    return translationString !== undefined ? t(translationString) : t('Unknown error.');
};
