import { CombinedError } from '@urql/core';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { useEffect } from 'react';

export const useHandleCartErrors = (resultErrors: CombinedError | undefined): void => {
    useEffect(() => {
        if (resultErrors === undefined) {
            return;
        }

        // TODO refactor
        for (const error of resultErrors.graphQLErrors) {
            if (error.extensions?.validation === undefined) {
                return;
            }

            for (const invalidFieldName in error.extensions.validation) {
                for (const validationError of error.extensions.validation[invalidFieldName]) {
                    showErrorMessage(validationError.message);
                }
            }
        }
    }, [resultErrors]);
};
