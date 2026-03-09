import { TextInput } from './TextInput';
import eyeIcon from '/public/svg/eye.svg';
import { Image } from 'components/Basic/Image/Image';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { InputHTMLAttributes, ReactElement, ReactNode, useState } from 'react';
import { Control, useController } from 'react-hook-form';
import { twJoin } from 'tailwind-merge';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<InputHTMLAttributes<HTMLInputElement>, never, 'name' | 'autoComplete'>;

type PasswordInputProps = NativeProps & {
    label: ReactNode;
    inputSize?: 'small' | 'default';
    'aria-label'?: string;
    'aria-labelledby'?: string;
};

type PasswordInputControlledProps = {
    name: string;
    render?: (input: ReactElement) => ReactElement<any, any> | null;
    gridClassName?: string;
    passwordInputProps: PasswordInputProps;
    control: Control<any>;
    formName: string;
};

export const PasswordInputControlled: FC<PasswordInputControlledProps> = ({
    name,
    render,
    gridClassName,
    control,
    passwordInputProps,
    formName,
}) => {
    const {
        fieldState: { error },
        field: { ref: fieldRef, value, onBlur, onChange },
    } = useController({ name, control });
    const passwordInputId = formName + '-' + name;

    const [inputType, setInputType] = useState<'text' | 'password'>('password');

    const togglePasswordVisibilityHandler = () => {
        setInputType((currentInputType) => (currentInputType === 'password' ? 'text' : 'password'));
    };

    const element = (
        <>
            <TextInput
                required
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
                <Image
                    alt="eye icon"
                    src={eyeIcon}
                    className={twJoin(
                        'absolute top-1/2 right-4 w-6 -translate-y-1/2 cursor-pointer',
                        inputType === 'text' && 'opacity-50',
                    )}
                    onClick={togglePasswordVisibilityHandler}
                />
            </TextInput>
            <FormLineError error={error} inputType="text-input-password" textInputSize={passwordInputProps.inputSize} />
        </>
    );

    if (render) {
        return render(element);
    }

    return <FormLine className={gridClassName}>{element}</FormLine>;
};
