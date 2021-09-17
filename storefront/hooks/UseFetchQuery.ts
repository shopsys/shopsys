/* eslint-disable @typescript-eslint/no-explicit-any */

import { getUserFriendlyErrors, ParsedErrors } from '../connectors/lib/friendlyErrorMessageParser';
import { useQuery, UseQueryArgs, UseQueryState } from 'urql';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { useEffect } from 'react';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

type ShopsysUseQueryState = UseQueryState & { parsedErrors: ParsedErrors };

export const useFetchQuery = (query: UseQueryArgs): ShopsysUseQueryState => {
    const t = useTypedTranslationFunction();
    const result: ShopsysUseQueryState = {
        ...useQuery(query)[0],
        parsedErrors: {
            applicationError: undefined,
            userError: undefined,
        },
    };

    useEffect(() => {
        if (result.error === undefined) {
            return;
        }

        result.parsedErrors = getUserFriendlyErrors(result.error, t);

        if (result.parsedErrors.applicationError === undefined) {
            return;
        }

        showErrorMessage(result.parsedErrors.applicationError);
    }, [result.fetching]);

    return result;
};
