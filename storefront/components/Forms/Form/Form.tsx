import { FC, FormEvent, FormHTMLAttributes } from 'react';
import { useFormContext } from 'react-hook-form';

type NativeProps = FormHTMLAttributes<HTMLFormElement>;

type FormProps = NativeProps;

export const Form: FC<FormProps> = (props) => {
    const formProviderMethods = useFormContext();
    const controlledOnSubmitHandler = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        // formProviderMethods may be null probably when it is not used in FormProvider context - see https://github.com/react-hook-form/react-hook-form/discussions/3894
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
        if (!formProviderMethods?.formState.isSubmitting && props.onSubmit !== undefined) {
            props.onSubmit(event);
        }
    };
    return <form {...props} onSubmit={controlledOnSubmitHandler} noValidate></form>;
};
