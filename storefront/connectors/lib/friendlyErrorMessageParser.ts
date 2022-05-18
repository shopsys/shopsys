import { Translate } from 'next-translate';
import { ParsedErrors, ValidationErrors } from 'types/error';
import { CombinedError } from 'urql';

export enum ApplicationErrors {
    DEFAULT = 'DEFAULT',
    CART_NOT_FOUND = 'CART_NOT_FOUND',
}

export const getUserFriendlyErrors = (originalError: CombinedError, t: Translate): ParsedErrors => {
    const errors: ParsedErrors = {};

    if (originalError.networkError) {
        errors.networkError = t('Could not connect to server. Check your network.') as string;
    } else if (originalError.graphQLErrors.length > 0) {
        for (const error of originalError.graphQLErrors) {
            if (
                error.extensions !== undefined &&
                Object.prototype.hasOwnProperty.call(error.extensions, 'validation')
            ) {
                const mappedValidationErrors: ValidationErrors = {};

                for (const errorName in error.extensions.validation) {
                    const newErrorName = errorName.replace('input.', '');
                    mappedValidationErrors[newErrorName] = error.extensions.validation[errorName][0];
                }

                errors.userError = { validation: mappedValidationErrors };
                continue;
            }

            if (error.extensions?.code === 'cart-unavailable') {
                errors.applicationError = { type: ApplicationErrors.CART_NOT_FOUND, message: t('Cart not found') };
                continue;
            }

            errors.applicationError = { type: ApplicationErrors.DEFAULT, message: t('Unknown error.') };
        }
    } else {
        errors.applicationError = { type: ApplicationErrors.DEFAULT, message: t('Unknown error.') };
    }

    return errors;
};
