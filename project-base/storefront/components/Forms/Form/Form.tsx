import { FormEvent, FormHTMLAttributes } from 'react';
import { useFormContext } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<FormHTMLAttributes<HTMLFormElement>, never, 'onSubmit' | 'style'>;

type FormProps = NativeProps;

export const Form: FC<FormProps> = ({ onSubmit, style, children, className, tid }) => {
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
        <form noValidate className={className} style={style} tid={tid} onSubmit={controlledOnSubmitHandler}>
            {children}
        </form>
    );
};

export const FormContentWrapper: FC = ({ children, className }) => {
    return (
        <div className={twMergeCustom('w-full max-w-3xl rounded-xl bg-backgroundMore px-4 vl:px-8', className)}>
            {children}
        </div>
    );
};

export const FormBlockWrapper: FC = ({ children, className }) => {
    return (
        <div className={twMergeCustom('border-b border-b-borderAccent py-4 last:border-b-0 vl:py-8', className)}>
            {children}
        </div>
    );
};

export const FormButtonWrapper: FC = ({ children, className }) => {
    return <div className={twMergeCustom('mt-6 flex justify-center', className)}>{children}</div>;
};

export const FormHeading: FC = ({ children, className }) => {
    return <h4 className={twMergeCustom('mb-4', className)}>{children}</h4>;
};
