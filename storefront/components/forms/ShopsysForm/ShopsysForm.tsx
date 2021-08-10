import { FormHTMLAttributes, ReactElement } from 'react';
import { FormProvider, Resolver, useForm } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from '../../../typeHelpers/ExtractNativePropsFromDefault';
import { getUserFriendlyErrors } from '../../../connectors/lib/friendlyErrorMessageParser';
import { OperationResult } from 'urql';
import { useTranslation } from 'react-i18next';

type NativeProps = ExtractNativePropsFromDefault<FormHTMLAttributes<HTMLFormElement>, 'children', never>;

/**
 * A wrapper element for forms. This element outsources the logic behind form's validation and submission.
 */
function ShopsysForm(
    props: {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        onSubmitHandler: (variables?: any | undefined) => Promise<OperationResult<any, any>>;
        resolver: Resolver;
    } & NativeProps,
): ReactElement {
    const { t } = useTranslation();
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
