import {
    ApplicationErrorsType,
    isFlashMessageError,
    isNoFlashMessageError,
    isNoLogError,
} from 'helpers/errors/applicationErrors';
import { getErrorMessage } from 'helpers/errors/errorMessageMapper';
import { Translate } from 'next-translate';
import { ParsedErrors, ValidationErrors } from 'types/error';
import { CombinedError } from 'urql';

export const getUserFriendlyErrors = (originalError: CombinedError, t: Translate): ParsedErrors => {
    const errors: ParsedErrors = {};

    if (originalError.networkError) {
        errors.networkError = t('Could not connect to server. Check your network.') as string;
    } else if (originalError.graphQLErrors.length > 0) {
        for (const error of originalError.graphQLErrors) {
            if ('validation' in error.extensions) {
                const errorExtensions = error.extensions as {
                    validation: {
                        [fieldName: string]: {
                            message: string;
                            code: string;
                        }[];
                    };
                };
                const mappedValidationErrors: ValidationErrors = {};

                for (const errorName in errorExtensions.validation) {
                    const newErrorName = errorName.replace('input.', '');
                    mappedValidationErrors[newErrorName] = errorExtensions.validation[errorName][0];
                }

                errors.userError = { validation: mappedValidationErrors };
                continue;
            }

            if ('userCode' in error.extensions) {
                const errorExtensions = error.extensions as { userCode: ApplicationErrorsType };
                if (isNoLogError(errorExtensions.userCode) || isNoFlashMessageError(errorExtensions.userCode)) {
                    errors.applicationError = {
                        type: errorExtensions.userCode,
                        message: error.message,
                    };
                    continue;
                }

                if (isFlashMessageError(errorExtensions.userCode)) {
                    errors.applicationError = {
                        type: errorExtensions.userCode,
                        message: getErrorMessage(errorExtensions.userCode, t),
                    };
                    continue;
                }
            }

            errors.applicationError = { type: 'default', message: t('Unknown error.') };
        }
    } else {
        errors.applicationError = { type: 'default', message: t('Unknown error.') };
    }

    return errors;
};
