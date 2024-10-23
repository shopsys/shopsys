import { DeepPartial, FieldValues, Resolver, useForm, UseFormReturn } from 'react-hook-form';

export const useShopsysForm = <T extends FieldValues>(
    resolver: Resolver<T> | undefined,
    defaultValues: DeepPartial<T>,
): UseFormReturn<T> =>
    useForm<T>({
        mode: 'all',
        reValidateMode: 'onChange',
        criteriaMode: 'firstError',
        resolver: resolver,
        defaultValues: defaultValues,
        delayError: 500,
    });
