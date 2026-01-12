import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { Translate } from 'next-translate';
import { FieldValues, Path, UseFormReturn } from 'react-hook-form';
import { CombinedError } from 'urql';
import { isFlashMessageError } from 'utils/errors/applicationErrors';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';

export const handleFormErrors = <T extends FieldValues>(
    error: CombinedError | undefined,
    formProviderMethods: UseFormReturn<T>,
    t: Translate,
    errorMessage: string | undefined = undefined,
    fields: Record<string, { name: string }> | undefined = undefined,
    origin: GtmMessageOriginType = GtmMessageOriginType.other,
): void => {
    if (error === undefined) {
        return;
    }

    const { userError, applicationError } = getUserFriendlyErrors(error, t);

    // Only show toast for flash-message errors, respecting verbosity levels
    if (applicationError !== undefined && isFlashMessageError(applicationError.type)) {
        showErrorMessage(errorMessage !== undefined ? errorMessage : applicationError.message, origin, {
            errorType: applicationError.type,
        });
    }

    if (userError?.validation !== undefined) {
        const formFieldNames =
            fields !== undefined
                ? Object.keys(fields).map((fieldKey) => fields[fieldKey].name)
                : Object.keys(formProviderMethods.getValues());

        for (const fieldName in userError.validation) {
            if (formFieldNames.includes(fieldName)) {
                formProviderMethods.setError(fieldName as Path<T>, userError.validation[fieldName]);
                continue;
            }
            showErrorMessage(userError.validation[fieldName].message, origin);
        }
    }
};
