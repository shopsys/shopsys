import { FC, FormEvent, FormHTMLAttributes } from 'react';
import { useFormContext } from 'react-hook-form';

type FormProps = FormHTMLAttributes<HTMLFormElement>;

const Form: FC<FormProps> = (props) => {
    const formProviderMethods = useFormContext();
    const controlledOnSubmitHandler = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        if (!formProviderMethods?.formState.isSubmitting && props.onSubmit !== undefined) {
            props.onSubmit(event);
        }
    };
    return <form {...props} onSubmit={controlledOnSubmitHandler}></form>;
};

export default Form;
