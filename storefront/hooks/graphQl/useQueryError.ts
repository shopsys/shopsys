import { showErrorMessage } from 'components/Helpers/toasts';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { logException } from 'helpers/errors/logException';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useEffect } from 'react';
import { UseQueryResponse } from 'urql';

export const useQueryError = <T>([queryResponse, refetchFunction]: UseQueryResponse<T>): UseQueryResponse<T> => {
    const t = useTypedTranslationFunction();

    useEffect(() => {
        if (queryResponse.error === undefined) {
            return;
        }

        const parsedErrors = getUserFriendlyErrors(queryResponse.error, t);

        logException(queryResponse.error);

        if (parsedErrors.applicationError === undefined) {
            return;
        }

        showErrorMessage(parsedErrors.applicationError.message);
    }, [queryResponse.error, t]);

    return [queryResponse, refetchFunction];
};
