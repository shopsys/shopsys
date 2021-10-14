import { CombinedError } from '@urql/core';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { useEffect } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const useHandleFormValidationErrors = (
    error: CombinedError | undefined,
    formProviderMethods: UseFormReturn,
): void => {
    const t = useTypedTranslationFunction();
    useEffect(() => {
        if (error !== undefined) {
            const { userError, applicationError } = getUserFriendlyErrors(error, t);
            for (const error in userError) {
                formProviderMethods.setError(error, { message: userError[error][0]?.message });
            }
            if (applicationError !== undefined) {
                showErrorMessage(applicationError);
            }
        }
    }, [error]);
};
