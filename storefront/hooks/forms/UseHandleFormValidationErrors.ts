import { Path, UseFormReturn } from 'react-hook-form';
import { CombinedError } from '@urql/core';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { useEffect } from 'react';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const useHandleFormValidationErrors = <T>(
    error: CombinedError | undefined,
    formProviderMethods: UseFormReturn<T>,
): void => {
    const t = useTypedTranslationFunction();
    useEffect(() => {
        if (error !== undefined) {
            const { userError, applicationError } = getUserFriendlyErrors(error, t);
            for (const error in userError) {
                formProviderMethods.setError(error as Path<T>, { message: userError[error][0]?.message });
            }
            if (applicationError !== undefined) {
                showErrorMessage(applicationError);
            }
        }
    }, [error]);
};
