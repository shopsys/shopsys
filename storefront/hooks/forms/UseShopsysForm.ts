import { FieldValues, Resolver, useForm, UseFormReturn } from 'react-hook-form';

export const useShopsysForm = (
    resolver: Resolver | undefined,
    defaultValues: FieldValues,
): UseFormReturn<FieldValues> =>
    useForm({
        mode: 'all',
        reValidateMode: 'onChange',
        criteriaMode: 'firstError',
        resolver: resolver,
        defaultValues: defaultValues,
    });
