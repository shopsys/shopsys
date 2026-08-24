import { useEffect } from 'react';
import { UseFormReturn, useWatch } from 'react-hook-form';
import { logException } from 'utils/errors/logException';

export type HoneyPot<TFieldName extends string = string> = {
    fieldName: TFieldName;
    value: string;
};

export const useHoneyPot = <TFieldName extends string>(
    formProviderMethods: UseFormReturn<any>,
    fieldName: TFieldName,
): HoneyPot<TFieldName> => {
    const value = useWatch({ name: fieldName, control: formProviderMethods.control });

    useEffect(() => {
        // an unregistered field means the hidden input was never rendered; getValues is read
        // instead of the value above, which useWatch does not see on the very first render
        if (formProviderMethods.getValues(fieldName) === undefined) {
            logException(
                `Honey pot field "${fieldName}" was not rendered. Pass the honeyPot object to the Form component.`,
            );
        }
    }, []);

    return {
        fieldName,
        value: value ?? '',
    };
};
