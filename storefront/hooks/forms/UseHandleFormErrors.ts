import { CombinedError } from '@urql/core';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useEffect } from 'react';
import { Path, UseFormReturn } from 'react-hook-form';

// TODO: předělat z hooku na normální handler
export const useHandleFormErrors = <T>(
    error: CombinedError | undefined,
    formProviderMethods: UseFormReturn<T>,
    errorMessage?: string,
    fields?: Record<string, { name: string }>,
): void => {
    const t = useTypedTranslationFunction();
    useEffect(() => {
        if (error === undefined) {
            return;
        }

        const { userError, applicationError } = getUserFriendlyErrors(error, t);

        if (applicationError !== undefined) {
            showErrorMessage(errorMessage !== undefined ? errorMessage : applicationError.message);
        }

        if (userError?.validation !== undefined) {
            for (const fieldName in userError.validation) {
                if (
                    fields !== undefined &&
                    Object.keys(fields).some((fieldKey) => fields[fieldKey].name === fieldName)
                ) {
                    formProviderMethods.setError(fieldName as Path<T>, userError.validation[fieldName]);
                    continue;
                }
                showErrorMessage(userError.validation[fieldName].message);
            }
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [error, t]);
};
