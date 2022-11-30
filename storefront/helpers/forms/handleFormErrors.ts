import { CombinedError } from '@urql/core';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { Translate } from 'next-translate';
import { FieldValues, Path, UseFormReturn } from 'react-hook-form';
import { GtmMessageOriginType } from 'types/gtm';

export const handleFormErrors = <T extends FieldValues>(
    error: CombinedError | undefined,
    formProviderMethods: UseFormReturn<T>,
    origin: GtmMessageOriginType,
    t: Translate,
    errorMessage?: string,
    fields?: Record<string, { name: string }>,
): void => {
    if (error === undefined) {
        return;
    }

    const { userError, applicationError } = getUserFriendlyErrors(error, t);

    if (applicationError !== undefined) {
        showErrorMessage(errorMessage !== undefined ? errorMessage : applicationError.message, origin);
    }

    if (userError?.validation !== undefined) {
        for (const fieldName in userError.validation) {
            if (fields !== undefined && Object.keys(fields).some((fieldKey) => fields[fieldKey].name === fieldName)) {
                formProviderMethods.setError(fieldName as Path<T>, userError.validation[fieldName]);
                continue;
            }
            showErrorMessage(userError.validation[fieldName].message, origin);
        }
    }
};
