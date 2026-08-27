import { useEffect } from 'react';
import { UseFormReturn } from 'react-hook-form';
import { logException } from 'utils/errors/logException';

export type HoneyPot = {
    fieldName: string;
    getInput: () => Record<string, string>;
};

export const useHoneyPot = (formProviderMethods: UseFormReturn<any>, fieldName: string): HoneyPot => {
    useEffect(() => {
        // an unregistered field means the hidden input was never rendered, so the form
        // would look protected and send nothing
        if (formProviderMethods.getValues(fieldName) === undefined) {
            logException(
                `Honey pot field "${fieldName}" was not rendered. Pass the honeyPot object to the Form component.`,
            );
        }
    }, []);

    return {
        fieldName,
        getInput: () => ({ [fieldName]: formProviderMethods.getValues(fieldName) ?? '' }),
    };
};
