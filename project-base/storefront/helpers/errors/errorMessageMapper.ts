import { ApplicationErrors, ApplicationErrorsType } from './applicationErrors';
import { Translate } from 'next-translate';
import { ApplicationIgnoredErrors } from './ignoredErrors';

type ApplicationErrorsWithoutIgnoredErrorsType = Exclude<
    ApplicationErrorsType,
    (typeof ApplicationIgnoredErrors)[number]
>;

const getErrorMessageTranslationString = (
    errorCode: ApplicationErrorsWithoutIgnoredErrorsType,
    t: Translate,
): string | undefined => {
    const ERROR_MESSAGES: Record<ApplicationErrorsWithoutIgnoredErrorsType, string | undefined> = {
        [ApplicationErrors.default]: t('Unknown error.'),
        [ApplicationErrors['cart-not-found']]: t('Cart not found.'),
        [ApplicationErrors['max-allowed-limit']]: t('Max allowed limit reached.'),
        [ApplicationErrors['packetery-address-id-invalid']]: t('Invalid Packetery address id.'),
        [ApplicationErrors['invalid-credentials']]: t('Invalid credentials.'),
        [ApplicationErrors['invalid-refresh-token']]: t('Invalid refresh token.'),
        [ApplicationErrors['order-emails-not-sent']]: t('Automatic order emails was not sent.'),
        [ApplicationErrors['order-empty-cart']]: t('Cart is empty.'),
        [ApplicationErrors['personal-data-request-type-invalid']]: t('Invalid request type.'),
        [ApplicationErrors['blog-category-not-found']]: t('Category not found.'),
        [ApplicationErrors['image-type-invalid']]: t('Invalid image type.'),
        [ApplicationErrors['image-size-invalid']]: t('Invalid image size.'),
        [ApplicationErrors['order-not-found']]: t('Order not found.'),
        [ApplicationErrors['personal-data-hash-invalid']]: t('Invalid hash.'),
        [ApplicationErrors['product-price-missing']]: t('Product price is missing.'),
        [ApplicationErrors['store-not-found']]: t('Store not found.'),
        [ApplicationErrors['product-not-found']]: t('Product not found.'),
        [ApplicationErrors['handling-with-logged-customer-comparison']]: t('Product not found.'),
        [ApplicationErrors['comparison-not-found']]: t('Comparison not found.'),
        [ApplicationErrors['compared-item-not-found']]: t('Compared product not found.'),
        [ApplicationErrors['compared-item-already-exists']]: t('Compared product is already compared.'),
    };

    return ERROR_MESSAGES[errorCode];
};

export const hasErrorMessage = (errorCode: string, t: Translate): boolean => {
    return getErrorMessageTranslationString(errorCode as ApplicationErrorsWithoutIgnoredErrorsType, t) !== undefined;
};

export const getErrorMessage = (errorCode: string, t: Translate): string => {
    const translationString = getErrorMessageTranslationString(
        errorCode as ApplicationErrorsWithoutIgnoredErrorsType,
        t,
    );

    return translationString !== undefined ? t(translationString) : t('Unknown error.');
};
