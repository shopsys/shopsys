import { CombinedError } from '@urql/core';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { logException } from 'helpers/errors/logException';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useEffect } from 'react';

export const useQueryError = (error: CombinedError | undefined): void => {
    const t = useTypedTranslationFunction();
    useEffect(() => {
        if (error === undefined) {
            return;
        }

        const parsedErrors = getUserFriendlyErrors(error, t);

        logException(error);

        if (parsedErrors.applicationError === undefined) {
            return;
        }

        showErrorMessage(parsedErrors.applicationError.message);
    }, [error, t]);
};
