/* eslint-disable @typescript-eslint/no-explicit-any */

import { getUserFriendlyErrors, ParsedErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { useQuery, UseQueryArgs, UseQueryState } from 'urql';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { useEffect } from 'react';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ShopsysUseQueryState = UseQueryState & { parsedErrors: ParsedErrors };

export const useFetchQuery = (query: UseQueryArgs): ShopsysUseQueryState => {
    const t = useTypedTranslationFunction();
    const result: ShopsysUseQueryState = {
        ...useQuery(query)[0],
        parsedErrors: {},
    };

    useEffect(() => {
        if (result.error === undefined) {
            return;
        }

        result.parsedErrors = getUserFriendlyErrors(result.error, t);

        if (result.parsedErrors.applicationError !== undefined) {
            showErrorMessage(result.parsedErrors.applicationError.message);
        }
    }, [result.fetching]);

    return result;
};
