import { CombinedError } from 'urql';
import { TFunction } from 'react-i18next';

export type ParsedErrors = {
    applicationError: undefined | string;
    userError: undefined | { [key: string]: { message: string; code: string }[] };
};

export const getUserFriendlyErrors = (originalError: CombinedError, t: TFunction): ParsedErrors => {
    const errors: ParsedErrors = {
        applicationError: undefined,
        userError: undefined,
    };

    if (originalError.networkError) {
        errors.applicationError = t('Could not connect to server. Check your network.');
    } else if (originalError.graphQLErrors.length > 0) {
        originalError.graphQLErrors.forEach((error) => {
            if (error.message === 'validation') {
                for (const errorName in error?.extensions?.validation) {
                    delete Object.assign(error?.extensions?.validation, {
                        [errorName.replace('input.', '')]: error?.extensions?.validation[errorName],
                    })[errorName];
                }
                errors.userError = error?.extensions?.validation;
            } else {
                // @todo: add real exception to error handler to deal with this
                errors.applicationError = t('Hooops, someting wrong happend.');
            }
        });
    } else {
        errors.applicationError = t('Unknown error.');
    }

    return errors;
};
