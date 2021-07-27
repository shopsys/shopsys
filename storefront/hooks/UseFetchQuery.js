import { useContext, useEffect } from 'react';
import { SsfwGlobalErrorContext } from '../components/SsfwGlobalErrorProvider/SsfwGlobalErrorProvider';
import { useQuery } from 'urql';
import { useTranslation } from 'react-i18next';

export const useFetchQuery = (query) => {
    const { t } = useTranslation();
    const { state, setState } = useContext(SsfwGlobalErrorContext);
    const [result] = useQuery(query);

    useEffect(() => {
        result.parsedErrors = undefined;
        if (!result.error) {
            return;
        }

        const parsedErrors = getUserFriendlyErrorMessage(result.error, t);
        result.parsedErrors = parsedErrors;

        if (!parsedErrors.applicationError) {
            return;
        }

        const stateErrors = [...state];
        stateErrors.push(parsedErrors.applicationError);
        setState(stateErrors);
    }, [result.fetching]);
    return result;
};

const getUserFriendlyErrorMessage = (originalError, t) => {
    let errors = {
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
