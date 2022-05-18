import { captureException } from '@sentry/nextjs';
import { CombinedError } from '@urql/core';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useEffect } from 'react';

export const useQueryError = (error: CombinedError | undefined): void => {
    const t = useTypedTranslationFunction();
    useEffect(() => {
        if (error === undefined) {
            return;
        }

        const parsedErrors = getUserFriendlyErrors(error, t);

        captureException(error);

        if (parsedErrors.applicationError === undefined) {
            return;
        }

        showErrorMessage(parsedErrors.applicationError.message);
    }, [error, t]);
};
