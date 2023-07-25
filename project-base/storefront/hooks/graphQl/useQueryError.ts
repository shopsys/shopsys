import { showErrorMessage } from 'components/Helpers/toasts';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { logException } from 'helpers/errors/logException';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { Translate } from 'next-translate';
import { useEffect } from 'react';
import { CombinedError, UseQueryResponse } from 'urql';

export const useQueryError = <T>([queryResponse, refetchFunction]: UseQueryResponse<T>): UseQueryResponse<T> => {
    const t = useTypedTranslationFunction();

    useEffect(() => {
        handleQueryError(queryResponse.error, t);
    }, [queryResponse.error, t]);

    return [queryResponse, refetchFunction];
};

export const handleQueryError = (error: CombinedError | undefined, t: Translate) => {
    if (error === undefined) {
        return;
    }

    const parsedErrors = getUserFriendlyErrors(error, t);

    logException(error);

    if (parsedErrors.applicationError === undefined) {
        return;
    }

    showErrorMessage(parsedErrors.applicationError.message);
};
