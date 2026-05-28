import { EyeCrossedIcon } from 'components/Basic/Icon/EyeCrossedIcon';
import { EyeIcon } from 'components/Basic/Icon/EyeIcon';
import { FormLine, FormLineWidth } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { InputHTMLAttributes, ReactElement, ReactNode, useState } from 'react';
import { Control, useController } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { TextInput } from './TextInput';

type NativeProps = ExtractNativePropsFromDefault<InputHTMLAttributes<HTMLInputElement>, never, 'name' | 'autoComplete'>;

type PasswordInputProps = NativeProps & {
    label: ReactNode;
    inputSize?: 'small' | 'default';
    'aria-describedby'?: string;
    'aria-label'?: string;
    'aria-labelledby'?: string;
};

type PasswordInputControlledProps = {
    name: string;
    render?: (input: ReactElement) => ReactElement<any, any> | null;
    width?: FormLineWidth;
    passwordInputProps: PasswordInputProps;
    control: Control<any>;
    formName: string;
};

export const PasswordInputControlled: FC<PasswordInputControlledProps> = ({
    name,
    render,
    width,
    control,
    passwordInputProps,
    formName,
}) => {
    const { t } = useTranslation();
    const {
        fieldState: { error },
        field: { ref: fieldRef, value, onBlur, onChange },
    } = useController({ name, control });
    const passwordInputId = `${formName}-${name}`;
    const errorId = `${passwordInputId}-error`;
    const describedBy = [passwordInputProps['aria-describedby'], error ? errorId : undefined].filter(Boolean).join(' ');

    const [inputType, setInputType] = useState<'text' | 'password'>('password');
    const PasswordVisibilityIcon = inputType === 'text' ? EyeCrossedIcon : EyeIcon;
    const passwordVisibilityLabel =
        inputType === 'text'
            ? t('Hide password', { ns: 'accessibility' })
            : t('Show password', { ns: 'accessibility' });

    const togglePasswordVisibilityHandler = () => {
        setInputType((currentInputType) => (currentInputType === 'password' ? 'text' : 'password'));
    };

    const element = (
        <>
            <TextInput
                required
                aria-describedby={describedBy || undefined}
                aria-invalid={error ? true : undefined}
                aria-label={passwordInputProps['aria-label']}
                aria-labelledby={passwordInputProps['aria-labelledby']}
                autoComplete={passwordInputProps.autoComplete}
                hasError={!!error}
                id={passwordInputId}
                inputSize={passwordInputProps.inputSize}
                label={passwordInputProps.label}
                name={name}
                ref={fieldRef}
                type={inputType}
                value={value}
                onBlur={onBlur}
                onChange={onChange}
            >
                <button
                    aria-label={passwordVisibilityLabel}
                    className="group absolute top-1/2 right-2 flex -translate-y-1/2 cursor-pointer items-center justify-center rounded-md p-1.5"
                    title={passwordVisibilityLabel}
                    type="button"
                    onClick={togglePasswordVisibilityHandler}
                >
                    <PasswordVisibilityIcon className="size-6 text-icon-less group-hover:text-icon-default" />
                </button>
            </TextInput>
            <FormLineError
                error={error}
                id={errorId}
                inputType="text-input-password"
                textInputSize={passwordInputProps.inputSize}
            />
        </>
    );

    if (render) {
        return render(element);
    }

    return <FormLine width={width}>{element}</FormLine>;
};
