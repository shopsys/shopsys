/* eslint-disable @typescript-eslint/no-explicit-any */
import { FC, useEffect } from 'react';
import { FormProvider, Resolver, SubmitHandler, useForm } from 'react-hook-form';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { OperationResult } from 'urql';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type FormProps = {
    defaultValues: { [key: string]: unknown };
    onSubmitHandler: (variables?: any | undefined) => Promise<OperationResult<any, any>>;
    onSuccessHandler: (...params: any) => any;
    resolver?: Resolver;
};

/**
 * A wrapper element for forms. This element outsources the logic behind form's validation and submission.
 */
const Form: FC<FormProps> = (props) => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useForm({
        mode: 'all',
        reValidateMode: 'onChange',
        criteriaMode: 'firstError',
        resolver: props.resolver,
        defaultValues: props.defaultValues,
    });
    useEffect(() => {
        if (formProviderMethods.formState.isSubmitSuccessful) {
            formProviderMethods.reset(props.defaultValues);
        }
    }, [formProviderMethods.formState, formProviderMethods.reset]);

    const formSubmitHandler: SubmitHandler<typeof props.defaultValues> = (data) => {
        if (document.activeElement instanceof HTMLElement) {
            document.activeElement.blur();
        }
        props.onSubmitHandler(data).then((result) => {
            if (result.error !== undefined) {
                const { userError, applicationError } = getUserFriendlyErrors(result.error, t);
                for (const error in userError) {
                    formProviderMethods.setError(error, { message: userError[error][0]?.message });
                }
                if (applicationError !== undefined) {
                    showErrorMessage(applicationError);
                }
            } else {
                props.onSuccessHandler();
            }
        });
    };

    return (
        <FormProvider {...formProviderMethods}>
            <form onSubmit={formProviderMethods.handleSubmit(formSubmitHandler)}>{props.children}</form>
        </FormProvider>
    );
};

/* @component */
export default Form;
