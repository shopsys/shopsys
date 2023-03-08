import { FormLineError } from '../Lib/FormLineError/FormLineError';
import { PasswordTextInputStyled } from './TextInput.style';
import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper/LabelWrapper';
import { InputHTMLAttributes, ReactElement, useCallback, useState } from 'react';
import { Control, useController } from 'react-hook-form';
import { twJoin } from 'tailwind-merge';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<InputHTMLAttributes<HTMLInputElement>, never, 'name'>;

type PasswordInputProps = NativeProps & {
    label: string;
    inputSize?: 'small' | 'default';
    testIdentifier?: string;
};

type PasswordInputControlledProps = {
    name: string;
    render: (input: JSX.Element) => ReactElement<any, any> | null;
    passwordInputProps: PasswordInputProps;
    control: Control<any>;
    formName: string;
};

export const PasswordInputControlled: FC<PasswordInputControlledProps> = ({
    name,
    render,
    control,
    passwordInputProps,
    formName,
}) => {
    const {
        fieldState: { invalid, error },
        field,
    } = useController({ name, control });
    const passwordInputId = formName + name;

    const [inputType, setInputType] = useState<'text' | 'password'>('password');

    const togglePasswordVisibilityHandler = useCallback(() => {
        setInputType((currentInputType) => (currentInputType === 'password' ? 'text' : 'password'));
    }, []);

    return render(
        <>
            <LabelWrapper label={passwordInputProps.label} required htmlFor={passwordInputId} inputType="text-input">
                <PasswordTextInputStyled
                    id={passwordInputId}
                    name={name}
                    hasError={invalid}
                    onBlur={field.onBlur}
                    type={inputType}
                    placeholder={passwordInputProps.label}
                    onChange={field.onChange}
                    ref={field.ref}
                    value={field.value}
                    inputSize={passwordInputProps.inputSize}
                    required
                    data-testid={passwordInputProps.testIdentifier}
                />
                <img
                    className={twJoin(
                        'absolute top-1/2 right-4 w-6 -translate-y-1/2 cursor-pointer',
                        inputType === 'text' && 'opacity-50',
                    )}
                    src="/svg/eye.svg"
                    onClick={togglePasswordVisibilityHandler}
                />
            </LabelWrapper>
            <FormLineError error={error} inputType="text-input-password" textInputSize={passwordInputProps.inputSize} />
        </>,
    );
};
