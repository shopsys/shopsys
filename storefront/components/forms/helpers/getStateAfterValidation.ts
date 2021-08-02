import { FieldValues, FormState } from 'react-hook-form';

export const getStateAfterValidation = (
    formState: FormState<FieldValues>,
    name: string,
    markSuccessfulWhenValid: boolean,
): 'error' | 'success' | undefined => {
    if (formState.errors[name]) {
        return 'error';
    }

    if (markSuccessfulWhenValid && formState.touchedFields[name]) {
        return 'success';
    }
};
