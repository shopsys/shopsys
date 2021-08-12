/* eslint-disable @typescript-eslint/no-explicit-any */

import { getUserFriendlyErrors, ParsedErrors } from '../connectors/lib/friendlyErrorMessageParser';
import { useContext, useEffect } from 'react';
import { useQuery, UseQueryArgs, UseQueryState } from 'urql';
import { ShopsysGlobalErrorContext } from '../context/ShopsysGlobalErrorProvider/ShopsysGlobalErrorProvider';
import { useTranslation } from 'react-i18next';

type ShopsysUseQueryState = UseQueryState & { parsedErrors: ParsedErrors };

export const useFetchQuery = (query: UseQueryArgs): ShopsysUseQueryState => {
    const { t } = useTranslation();
    const errorContext = useContext(ShopsysGlobalErrorContext);
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

        const stateErrors = [...errorContext.errors];
        stateErrors.push(result.parsedErrors.applicationError);
        errorContext.actions.setErrors(stateErrors);
    }, [result.fetching]);

    return result;
};
