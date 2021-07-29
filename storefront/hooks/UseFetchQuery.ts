/* eslint-disable @typescript-eslint/no-explicit-any */
import { CombinedError, useQuery, UseQueryArgs, UseQueryState } from 'urql';
import { useContext, useEffect } from 'react';
import { ShopsysGlobalErrorContext } from '../components/ShopsysGlobalErrorProvider/ShopsysGlobalErrorProvider';
import { TFunction } from 'i18next';
import { useTranslation } from 'react-i18next';

type ParsedErrors = {
    applicationError: undefined | string;
    userError: undefined | any;
};

type ShopsysUseQueryState = UseQueryState & { parsedErrors: ParsedErrors };

export const useFetchQuery = (query: UseQueryArgs): ShopsysUseQueryState => {
    const { t } = useTranslation();
    const { state, setState } = useContext(ShopsysGlobalErrorContext);
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

        result.parsedErrors = getUserFriendlyErrorMessage(result.error, t);

        if (result.parsedErrors.applicationError === undefined) {
            return;
        }

        const stateErrors = [...state];
        stateErrors.push(result.parsedErrors.applicationError);
        setState(stateErrors);
    }, [result.fetching]);

    return result;
};

const getUserFriendlyErrorMessage = (originalError: CombinedError, t: TFunction) => {
    const errors: ParsedErrors = {
        applicationError: undefined,
        userError: undefined,
    };

    if (originalError.networkError) {
        errors.applicationError = t('Could not connect to server. Check your network.');
    } else if (originalError.graphQLErrors.length > 0) {
        originalError.graphQLErrors.map((error) => {
            if (error.message === 'validation') {
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
