import { FormLineError } from '../Lib/FormLineError/FormLineError';
import { PasswordTextInputStyled, PasswordVisibilityToggleStyled } from './TextInput.style';
import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper/LabelWrapper';
import { FC, InputHTMLAttributes, ReactElement, useCallback, useState } from 'react';
import { Control, useController } from 'react-hook-form';
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
                <PasswordVisibilityToggleStyled
                    src="/svg/eye.svg"
                    isVisible={inputType === 'text'}
                    onClick={togglePasswordVisibilityHandler}
                />
            </LabelWrapper>
            <FormLineError error={error} inputType="text-input-password" textInputSize={passwordInputProps.inputSize} />
        </>,
    );
};
