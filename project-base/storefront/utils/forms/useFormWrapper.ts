import { DefaultValues, FieldValues, Resolver, useForm, UseFormReturn } from 'react-hook-form';

export const useFormWrapper = <T extends FieldValues>(
    resolver: Resolver<T> | undefined,
    defaultValues: DefaultValues<T>,
): UseFormReturn<T> =>
    useForm<T>({
        mode: 'onTouched',
        reValidateMode: 'onChange',
        criteriaMode: 'firstError',
        resolver: resolver,
        defaultValues: defaultValues,
        delayError: 500,
    });
