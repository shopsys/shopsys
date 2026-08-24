import { HoneyPotInput } from 'components/Forms/HoneyPot/HoneyPotInput';
import { FormHTMLAttributes, KeyboardEvent, SubmitEvent } from 'react';
import { useFormContext } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { useScrollToFirstError } from 'utils/forms/useScrollToFirstError';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<
    FormHTMLAttributes<HTMLFormElement>,
    never,
    'onSubmit' | 'style' | 'onKeyDown'
>;

type FormProps = NativeProps & {
    formName: string;
    renderHoneyPot?: boolean;
    preventEnterSubmission?: boolean;
};

export const Form: FC<FormProps> = ({
    onSubmit,
    style,
    children,
    className,
    tid,
    formName,
    renderHoneyPot = false,
    preventEnterSubmission = false,
    onKeyDown,
}) => {
    const formProviderMethods = useFormContext();

    const controlledOnSubmitHandler = (event: SubmitEvent<HTMLFormElement>) => {
        event.preventDefault();

        // formProviderMethods may be null probably when it is not used in FormProvider context - see https://github.com/react-hook-form/react-hook-form/discussions/3894
        if (!formProviderMethods?.formState.isSubmitting && onSubmit !== undefined) {
            onSubmit(event);
        }
    };

    const handleKeyDown = (event: KeyboardEvent<HTMLFormElement>) => {
        if (preventEnterSubmission && event.key === 'Enter' && event.target instanceof HTMLElement) {
            const targetTagName = event.target.tagName.toLowerCase();

            if (targetTagName !== 'button' && targetTagName !== 'textarea') {
                event.preventDefault();
            }
        }

        onKeyDown?.(event);
    };

    useScrollToFirstError(formName, formProviderMethods);

    return (
        /* biome-ignore lint/a11y/noNoninteractiveElementInteractions: Form submission belongs on the form element. */
        <form
            noValidate
            className={className}
            data-tid={tid}
            style={style}
            onKeyDown={handleKeyDown}
            onSubmit={controlledOnSubmitHandler}
        >
            {renderHoneyPot && <HoneyPotInput />}
            {children}
        </form>
    );
};

export const FormHeading: FC = ({ children, className }) => {
    return <legend className={twMergeCustom('h4 contents', className)}>{children}</legend>;
};

export const FormContentWrapper: FC = ({ children, className }) => {
    return <div className={twMergeCustom('flex w-full flex-col gap-5', className)}>{children}</div>;
};

export const FormBlockWrapper: FC = ({ children, className }) => {
    return (
        <fieldset
            className={twMergeCustom('flex flex-col gap-5 rounded-xl bg-background-more p-5 vl:px-20 py-8', className)}
        >
            {children}
        </fieldset>
    );
};

export const FormBlockAgreements: FC = ({ children, className }) => {
    return (
        <fieldset className={twMergeCustom('flex flex-col gap-4 px-5 vl:px-20 py-6', className)}>{children}</fieldset>
    );
};

export const FormButtonWrapper: FC = ({ children, className }) => {
    return <div className={twMergeCustom('flex justify-center', className)}>{children}</div>;
};
