import { ApplicationErrors } from 'helpers/errors/applicationErrors';
import { getErrorMessage, hasErrorMessage } from 'helpers/errors/errorMessageMapper';
import { ApplicationIgnoredErrors } from 'helpers/errors/ignoredErrors';
import { Translate } from 'next-translate';
import { ParsedErrors, ValidationErrors } from 'types/error';
import { CombinedError } from 'urql';

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

            if (error.extensions !== undefined) {
                if (ApplicationIgnoredErrors.includes(error.extensions.userCode)) {
                    continue;
                }

                if (hasErrorMessage(error.extensions.userCode, t)) {
                    errors.applicationError = {
                        type: error.extensions.userCode,
                        message: getErrorMessage(error.extensions.userCode, t),
                    };
                    continue;
                }
            }

            errors.applicationError = { type: ApplicationErrors.default, message: t('Unknown error.') };
        }
    } else {
        errors.applicationError = { type: ApplicationErrors.default, message: t('Unknown error.') };
    }

    return errors;
};
