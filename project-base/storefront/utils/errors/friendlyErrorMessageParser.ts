import { Translate } from 'next-translate';
import { ParsedErrors } from 'types/error';
import { CombinedError } from 'urql';
import {
    ApplicationErrorsType,
    isFlashMessageError,
    isNoFlashMessageError,
    isNoLogError,
} from 'utils/errors/applicationErrors';
import { getErrorMessage } from 'utils/errors/errorMessageMapper';
import { getFirstValidationErrorPerField, parseGraphqlError } from 'utils/errors/parseGraphqlError';

export const getUserFriendlyErrors = (originalError: CombinedError, t: Translate): ParsedErrors => {
    const errors: ParsedErrors = {};

    if (originalError.networkError) {
        errors.networkError = t('Could not connect to server. Check your network.') as string;
    } else if (originalError.graphQLErrors.length > 0) {
        for (const graphqlError of originalError.graphQLErrors) {
            const parsed = parseGraphqlError(graphqlError);

            if (parsed.validationErrors !== null) {
                errors.userError = {
                    validation: getFirstValidationErrorPerField(parsed.validationErrors, { stripInputPrefix: true }),
                };
                continue;
            }

            if (parsed.userCode !== null) {
                const userCode = parsed.userCode as ApplicationErrorsType;

                if (isNoLogError(userCode) || isNoFlashMessageError(userCode)) {
                    errors.applicationError = {
                        type: userCode,
                        message: parsed.message,
                    };

                    continue;
                }

                if (isFlashMessageError(userCode)) {
                    errors.applicationError = {
                        type: userCode,
                        message: getErrorMessage(userCode, t),
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
