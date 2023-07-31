import { ApplicationErrors, ApplicationErrorsType } from 'helpers/errors/applicationErrors';
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
                if (ApplicationIgnoredErrors.some((ignoredError) => ignoredError === errorExtensions.userCode)) {
                    continue;
                }

                if (hasErrorMessage(errorExtensions.userCode, t)) {
                    errors.applicationError = {
                        type: errorExtensions.userCode,
                        message: getErrorMessage(errorExtensions.userCode, t),
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
