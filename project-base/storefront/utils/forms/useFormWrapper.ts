import { DefaultValues, FieldValues, Resolver, UseFormReturn, useForm } from 'react-hook-form';

export const useFormWrapper = <TFieldValues extends FieldValues>(
    resolver: Resolver<TFieldValues, any, TFieldValues> | undefined,
    defaultValues: DefaultValues<TFieldValues>,
): UseFormReturn<TFieldValues, any, TFieldValues> =>
    useForm<TFieldValues, any, TFieldValues>({
        mode: 'onTouched',
        reValidateMode: 'onChange',
        criteriaMode: 'firstError',
        resolver: resolver,
        defaultValues: defaultValues,
        delayError: 500,
    });
