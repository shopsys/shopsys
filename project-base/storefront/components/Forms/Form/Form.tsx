import { FormEvent, FormHTMLAttributes } from 'react';
import { useFormContext } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<FormHTMLAttributes<HTMLFormElement>, never, 'onSubmit' | 'style'>;

type FormProps = NativeProps;

export const Form: FC<FormProps> = ({ onSubmit, style, children, className }) => {
    const formProviderMethods = useFormContext();
    const controlledOnSubmitHandler = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        // formProviderMethods may be null probably when it is not used in FormProvider context - see https://github.com/react-hook-form/react-hook-form/discussions/3894
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
        if (!formProviderMethods?.formState.isSubmitting && onSubmit !== undefined) {
            onSubmit(event);
        }
    };

    return (
        <form noValidate className={className} style={style} onSubmit={controlledOnSubmitHandler}>
            {children}
        </form>
    );
};
