/* eslint-disable @typescript-eslint/no-explicit-any */
import { FormHTMLAttributes, ReactElement } from 'react';
import { FormProvider, Resolver, useForm } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from '../../../typeHelpers/ExtractNativePropsFromDefault';
import { getUserFriendlyErrors } from '../../../connectors/lib/friendlyErrorMessageParser';
import { OperationResult } from 'urql';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';

type NativeProps = ExtractNativePropsFromDefault<FormHTMLAttributes<HTMLFormElement>, 'children', never>;

/**
 * A wrapper element for forms. This element outsources the logic behind form's validation and submission.
 */
function ShopsysForm(
    props: {
        onSubmitHandler: (variables?: any | undefined) => Promise<OperationResult<any, any>>;
        onSuccessHandler: (...params: any) => any;
        resolver: Resolver;
    } & NativeProps,
): ReactElement {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useForm({
        mode: 'all',
        reValidateMode: 'onChange',
        criteriaMode: 'firstError',
        resolver: props.resolver,
    });

    const formSubmitHandler = (data: never) => {
        props.onSubmitHandler(data).then((result) => {
            if (result.error !== undefined) {
                const { userError } = getUserFriendlyErrors(result.error, t);
                for (const error in userError) {
                    formProviderMethods.setError(error, { message: userError[error][0]?.message });
                }
            } else {
                formProviderMethods.reset();
                props.onSuccessHandler();
            }
        });
    };

    return (
        <FormProvider {...formProviderMethods}>
            <form onSubmit={formProviderMethods.handleSubmit(formSubmitHandler)}>{props.children}</form>
        </FormProvider>
    );
}

/* @component */
export default ShopsysForm;
