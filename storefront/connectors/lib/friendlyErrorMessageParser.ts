import { CombinedError } from 'urql';
import { TFunction } from 'react-i18next';
type ValidationErrors = { [fieldName: string]: { message: string; code: string } };
export enum ApplicationErrors {
    DEFAULT = 'DEFAULT',
    CART_NOT_FOUND = 'CART_NOT_FOUND',
}

const UUID4_REGULAR_EXP = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';

export type ParsedErrors = {
    networkError?: string;
    applicationError?: { type: ApplicationErrors; message: string };
    userError?: {
        validation?: ValidationErrors;
    };
};

export const getUserFriendlyErrors = (originalError: CombinedError, t: TFunction): ParsedErrors => {
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
                    mappedValidationErrors[newErrorName] = error?.extensions?.validation[errorName][0];
                }

                errors.userError = { validation: mappedValidationErrors };
                continue;
            }

            if (new RegExp('Cart "' + UUID4_REGULAR_EXP + '" is unavailable.').test(error.message)) {
                errors.applicationError = { type: ApplicationErrors.CART_NOT_FOUND, message: t('Cart not found') };
                continue;
            }

            if (
                /Cart "\b[0-9a-f]{8}\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\b[0-9a-f]{12}\b" is unavailable./.test(
                    error.message,
                )
            ) {
                errors.applicationError = { type: ApplicationErrors.CART_NOT_FOUND, message: t('Cart not found') };
            }

            errors.applicationError = { type: ApplicationErrors.DEFAULT, message: t('Unknown error.') };
        }
    } else {
        errors.applicationError = { type: ApplicationErrors.DEFAULT, message: t('Unknown error.') };
    }

    return errors;
};
