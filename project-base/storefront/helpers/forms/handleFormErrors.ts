import { showErrorMessage } from 'components/Helpers/toasts';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { Translate } from 'next-translate';
import { FieldValues, Path, UseFormReturn } from 'react-hook-form';
import { GtmMessageOriginType } from 'types/gtm/enums';
import { CombinedError } from 'urql';

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
